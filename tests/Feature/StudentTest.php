<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Course;
use App\Models\User;
use App\Notifications\StudentInvitation;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class StudentTest extends TestCase
{
    use DatabaseTransactions;

    public function test_existing_student_is_enrolled_without_an_invitation(): void
    {
        Notification::fake();
        $course = Course::factory()->create();
        $student = User::factory()->role('alumno')->create(['email' => 'ana@demo.test']);

        $this->actingAs($course->ponente)->post("/cursos/{$course->id}/alumnos", ['email' => ' ANA@demo.test '])
            ->assertRedirect("/cursos/{$course->id}/alumnos")->assertSessionHas('status', "Inscribimos a {$student->name}.");

        $this->assertSame('active', $course->enrollments()->sole()->status);
        $this->assertTrue($course->hasReader($student));
        Notification::assertNothingSent();

        $this->actingAs($course->ponente)->get("/cursos/{$course->id}/alumnos")->assertOk()->assertSee('ana@demo.test')->assertSee('Inscrito');
    }

    public function test_new_email_creates_a_student_invites_and_the_link_verifies_the_email(): void
    {
        Notification::fake();
        $course = Course::factory()->create(['title' => 'Estadística']);

        $this->actingAs($course->ponente)->post("/cursos/{$course->id}/alumnos", ['email' => 'nuevo@demo.test'])->assertSessionHasErrors('name');
        $this->assertDatabaseMissing('users', ['email' => 'nuevo@demo.test']);

        $this->actingAs($course->ponente)->post("/cursos/{$course->id}/alumnos", ['email' => 'nuevo@demo.test', 'name' => 'Luis Nuevo'])
            ->assertSessionHasNoErrors();

        $student = User::where('email', 'nuevo@demo.test')->sole();
        $this->assertTrue($student->hasRole('alumno'));
        $this->assertNull($student->email_verified_at);
        $this->assertTrue($course->hasReader($student));

        $token = null;
        Notification::assertSentTo($student, StudentInvitation::class, function (StudentInvitation $n) use ($student, &$token) {
            $mail = $n->toMail($student);
            $text = implode(' ', [...$mail->introLines, ...$mail->outroLines]);
            $token = $n->token;

            return str_contains($mail->subject, 'Estadística')
                && str_contains($mail->actionUrl, '/reset-password/'.$n->token)
                && str_contains($text, 'caduca en 60 minutos')
                && str_contains($text, '«¿Olvidaste tu contraseña?»');
        });

        auth()->logout();
        $this->post('/reset-password', ['token' => $token, 'email' => 'nuevo@demo.test', 'password' => 'una-clave-larga-123', 'password_confirmation' => 'una-clave-larga-123'])
            ->assertSessionHasNoErrors();
        $this->assertNotNull($student->fresh()->email_verified_at);
    }

    public function test_other_roles_and_deleted_accounts_are_rejected(): void
    {
        $course = Course::factory()->create();
        $ponente = User::factory()->role('ponente')->create();
        $gone = User::factory()->role('alumno')->create();
        $gone->delete();

        foreach ([$ponente->email, $gone->email, User::factory()->role('director')->create()->email] as $email) {
            $this->actingAs($course->ponente)->post("/cursos/{$course->id}/alumnos", ['email' => $email, 'name' => 'X'])->assertSessionHasErrors('email');
        }
        $this->assertSame(0, $course->enrollments()->count());
    }

    public function test_remove_is_audited_and_re_adding_reactivates(): void
    {
        $student = User::factory()->role('alumno')->create();
        $course = Course::factory()->enroll($student)->create();
        $enrollment = $course->enrollments()->sole();

        $this->actingAs($course->ponente)->delete("/inscripciones/{$enrollment->id}")->assertRedirect("/cursos/{$course->id}/alumnos");

        $this->assertSame('removed', $enrollment->fresh()->status);
        $this->assertNotNull($enrollment->fresh()->removed_at);
        $this->assertFalse($course->hasReader($student));
        $log = AuditLog::where('action', 'enrollment.removed')->where('entity_id', $enrollment->id)->sole();
        $this->assertSame($course->ponente_id, $log->user_id);
        $this->assertSame(['course_id' => $course->id, 'student_id' => $student->id], $log->meta);

        $this->actingAs($course->ponente)->post("/cursos/{$course->id}/alumnos", ['email' => $student->email])->assertSessionHasNoErrors();
        $this->assertSame('active', $enrollment->fresh()->status);
        $this->assertNull($enrollment->fresh()->removed_at);
        $this->assertSame(1, $course->enrollments()->count());
    }

    public function test_only_owner_or_super_admin_manage_students(): void
    {
        $student = User::factory()->role('alumno')->create();
        $course = Course::factory()->enroll($student)->create();
        $enrollment = $course->enrollments()->sole();

        foreach ([Course::factory()->create()->ponente, User::factory()->role('director')->create(), $student] as $user) {
            $this->actingAs($user)->get("/cursos/{$course->id}/alumnos")->assertForbidden();
            $this->actingAs($user)->post("/cursos/{$course->id}/alumnos", ['email' => 'x@demo.test', 'name' => 'X'])->assertForbidden();
            $this->actingAs($user)->delete("/inscripciones/{$enrollment->id}")->assertForbidden();
        }
        $this->assertSame('active', $enrollment->fresh()->status);
        $this->assertDatabaseMissing('users', ['email' => 'x@demo.test']);

        $this->actingAs(User::factory()->role('super_admin')->create())->delete("/inscripciones/{$enrollment->id}")->assertRedirect();
        $this->assertSame('removed', $enrollment->fresh()->status);
    }
}
