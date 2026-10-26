<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class RegistrationCatalogTest extends TestCase
{
    use DatabaseTransactions;

    private function form(array $extra = []): array
    {
        return array_merge(['name' => 'Paola Ruiz', 'email' => 'Paola@Demo.test', 'birth_date' => now()->subYears(20)->toDateString(),
            'password' => 'una-clave-larga-1', 'password_confirmation' => 'una-clave-larga-1', 'terms' => '1'], $extra);
    }

    public function test_adult_registers_as_alumno_and_terms_acceptance_is_logged(): void
    {
        $this->get('/register')->assertOk()->assertSee('Fecha de nacimiento');
        $this->post('/register', $this->form())->assertRedirect('/dashboard');

        $user = User::where('email', 'paola@demo.test')->sole();
        $this->assertTrue($user->hasRole('alumno'));
        $this->assertNull($user->email_verified_at);
        $this->assertAuthenticatedAs($user);
        $log = AuditLog::where('action', 'terms.accepted')->where('user_id', $user->id)->sole();
        $this->assertSame(config('site.terms_version'), $log->meta['version']);
    }

    public function test_minors_and_missing_terms_are_blocked_with_clear_messages(): void
    {
        $this->post('/register', $this->form(['birth_date' => now()->subYears(18)->addDay()->toDateString()]))
            ->assertSessionHasErrors(['birth_date' => 'Por ahora necesitas tener 18 años o más para crear una cuenta. Si eres menor de edad, tu escuela o tu ponente pueden inscribirte.']);
        $this->post('/register', $this->form(['terms' => null]))
            ->assertSessionHasErrors(['terms' => 'Para crear tu cuenta debes aceptar los términos y el aviso de privacidad.']);
        $this->post('/register', $this->form(['email' => User::factory()->create()->email]))->assertSessionHasErrors('email');
        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['email' => 'paola@demo.test']);

        // Exactamente 18 años hoy sí puede.
        $this->post('/register', $this->form(['birth_date' => now()->subYears(18)->toDateString()]))->assertSessionHasNoErrors();
    }

    private function needsCatalogColumn(): void
    {
        if (! Schema::hasColumn('courses', 'catalog_price')) {
            $this->markTestSkipped('Aplica database/changes/002_catalogo.sql en cursos_el_mai_test.');
        }
    }

    public function test_catalog_lists_only_published_courses_with_price(): void
    {
        $this->needsCatalogColumn();
        $free = Course::factory()->create(['title' => 'Curso gratis', 'catalog_price' => 0]);
        Course::factory()->create(['title' => 'Curso de pago', 'catalog_price' => 499]);
        Course::factory()->create(['title' => 'Curso privado', 'catalog_price' => null]);
        Course::factory()->draft()->create(['title' => 'Borrador listado', 'catalog_price' => 0]);

        $this->get('/catalogo')->assertOk()->assertSee('Curso gratis')->assertSee('$499 MXN')->assertSee('Gratis')
            ->assertDontSee('Curso privado')->assertDontSee('Borrador listado');
        $this->get("/catalogo/{$free->slug}")->assertOk()->assertSee('Crear cuenta');
        $this->get('/catalogo/'.Course::where('title', 'Curso privado')->value('slug'))->assertNotFound();
    }

    public function test_free_enrollment_and_simulated_payment_create_the_enrollment(): void
    {
        $this->needsCatalogColumn();
        $student = User::factory()->role('alumno')->create();
        $free = Course::factory()->create(['catalog_price' => 0]);
        $paid = Course::factory()->create(['catalog_price' => 499]);

        $this->actingAs($student)->post("/catalogo/{$free->slug}/inscripcion")->assertRedirect("/cursos/{$free->id}");
        $this->assertTrue($free->hasReader($student));

        $this->get("/catalogo/{$paid->slug}/pago")->assertOk()->assertSee('Pago simulado (modo prueba).');
        $this->post("/catalogo/{$paid->slug}/inscripcion")->assertRedirect("/cursos/{$paid->id}")
            ->assertSessionHas('status', 'Pago simulado registrado (modo prueba). Ya estás inscrito.');
        $this->assertTrue($paid->hasReader($student));
        $log = AuditLog::where('action', 'enrollment.simulated_payment')->sole();
        $this->assertSame(['course_id' => $paid->id, 'amount' => 499.0, 'simulated' => true], $log->meta);

        // Volver a inscribirse no duplica.
        $this->post("/catalogo/{$paid->slug}/inscripcion")->assertRedirect("/cursos/{$paid->id}");
        $this->assertSame(1, $paid->enrollments()->count());
    }

    public function test_who_cannot_self_enroll(): void
    {
        $this->needsCatalogColumn();
        $course = Course::factory()->create(['catalog_price' => 0]);
        $hidden = Course::factory()->create(['catalog_price' => null]);
        $removed = User::factory()->role('alumno')->create();
        $course->enrollments()->create(['user_id' => $removed->id, 'status' => 'removed']);

        $this->actingAs($removed)->post("/catalogo/{$course->slug}/inscripcion")->assertForbidden();
        $this->actingAs(User::factory()->role('ponente')->create())->post("/catalogo/{$course->slug}/inscripcion")->assertForbidden();
        $this->actingAs(User::factory()->role('alumno')->create())->post("/catalogo/{$hidden->slug}/inscripcion")->assertForbidden();
        $this->post("/catalogo/{$course->slug}/inscripcion")->assertForbidden();
        auth()->logout();
        $this->post("/catalogo/{$course->slug}/inscripcion")->assertRedirect('/login');
        $this->assertSame('removed', $course->enrollments()->sole()->status);
    }
}
