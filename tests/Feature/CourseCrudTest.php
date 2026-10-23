<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class CourseCrudTest extends TestCase
{
    use DatabaseTransactions;

    private function payload(array $overrides = []): array
    {
        return $overrides + ['title' => 'Introducción a la estadística', 'summary' => 'Lo básico.', 'status' => 'draft'];
    }

    public function test_ponente_creates_a_course_owned_by_them_even_if_they_send_another_ponente(): void
    {
        $ponente = User::factory()->role('ponente')->create();
        $other = User::factory()->role('ponente')->create();

        $this->actingAs($ponente)->get('/cursos/crear')->assertOk()->assertDontSee('Elige un ponente');

        $this->actingAs($ponente)->post('/cursos', $this->payload(['ponente_id' => $other->id]))
            ->assertSessionHasNoErrors()->assertRedirect();

        $course = Course::where('title', 'Introducción a la estadística')->sole();
        $this->assertTrue($course->ponente->is($ponente));
        $this->assertSame('draft', $course->status);
    }

    public function test_super_admin_must_choose_an_active_ponente(): void
    {
        $admin = User::factory()->role('super_admin')->create();
        $ponente = User::factory()->role('ponente')->create();

        $this->actingAs($admin)->get('/cursos/crear')->assertOk()->assertSee($ponente->email);

        $this->actingAs($admin)->post('/cursos', $this->payload())->assertSessionHasErrors('ponente_id');
        $this->actingAs($admin)->post('/cursos', $this->payload(['ponente_id' => $admin->id]))->assertSessionHasErrors('ponente_id');
        $this->actingAs($admin)->post('/cursos', $this->payload(['ponente_id' => User::factory()->role('ponente')->inactive()->create()->id]))->assertSessionHasErrors('ponente_id');

        $this->actingAs($admin)->post('/cursos', $this->payload(['ponente_id' => $ponente->id]))->assertSessionHasNoErrors();
        $this->assertTrue(Course::where('title', 'Introducción a la estadística')->sole()->ponente->is($ponente));
    }

    public function test_alumno_and_director_cannot_create_courses(): void
    {
        foreach (['alumno', 'director'] as $slug) {
            $user = User::factory()->role($slug)->create();
            $this->actingAs($user)->get('/cursos/crear')->assertForbidden();
            $this->actingAs($user)->post('/cursos', $this->payload())->assertForbidden();
        }
        $this->assertSame(0, Course::where('title', 'Introducción a la estadística')->count());
    }

    public function test_validation_rejects_bad_status_and_reversed_dates(): void
    {
        $ponente = User::factory()->role('ponente')->create();

        $this->actingAs($ponente)->post('/cursos', $this->payload(['title' => '', 'status' => 'secreto', 'starts_on' => '2026-11-10', 'ends_on' => '2026-11-01']))
            ->assertSessionHasErrors(['title', 'status', 'ends_on']);
    }

    public function test_owner_updates_other_ponente_and_director_get_403(): void
    {
        $course = Course::factory()->create(['title' => 'Original']);

        $this->actingAs($course->ponente)->get("/cursos/{$course->id}/editar")->assertOk()->assertSee('Original');
        $this->actingAs($course->ponente)->put("/cursos/{$course->id}", $this->payload(['title' => 'Nuevo título', 'status' => 'published']))
            ->assertRedirect("/cursos/{$course->id}/editar");
        $this->assertSame('Nuevo título', $course->fresh()->title);

        foreach (['ponente', 'director', 'alumno'] as $slug) {
            $user = User::factory()->role($slug)->create();
            $this->actingAs($user)->get("/cursos/{$course->id}/editar")->assertForbidden();
            $this->actingAs($user)->put("/cursos/{$course->id}", $this->payload(['title' => 'Hackeado']))->assertForbidden();
            $this->actingAs($user)->delete("/cursos/{$course->id}")->assertForbidden();
        }
        $this->assertSame('Nuevo título', $course->fresh()->title);
    }

    public function test_index_shows_only_own_courses_to_ponente_and_all_to_director(): void
    {
        $mine = Course::factory()->create(['title' => 'Curso propio']);
        Course::factory()->create(['title' => 'Curso ajeno']);

        $this->actingAs($mine->ponente)->get('/cursos')->assertOk()->assertSee('Curso propio')->assertDontSee('Curso ajeno');
        $this->actingAs(User::factory()->role('director')->create())->get('/cursos')->assertOk()
            ->assertSee('Curso propio')->assertSee('Curso ajeno')->assertDontSee('Nuevo curso');
        $this->actingAs(User::factory()->role('alumno')->create())->get('/cursos')->assertForbidden();
    }

    public function test_course_with_active_or_completed_students_is_not_deleted_only_archivable(): void
    {
        foreach (['active', 'completed'] as $status) {
            $course = Course::factory()->enroll(User::factory()->create(), $status)->create();

            $this->actingAs($course->ponente)->from("/cursos/{$course->id}/editar")->delete("/cursos/{$course->id}")
                ->assertRedirect("/cursos/{$course->id}/editar")
                ->assertSessionHasErrors(['delete' => 'Este curso tiene alumnos inscritos o que ya lo terminaron, así que no se puede borrar. Archívalo: cambia su estado a «Archivado» y guarda.']);

            $this->assertNotSoftDeleted($course);
        }
    }

    public function test_course_without_active_students_is_soft_deleted(): void
    {
        $course = Course::factory()->enroll(User::factory()->create(), 'removed')->create();

        $this->actingAs($course->ponente)->delete("/cursos/{$course->id}")->assertRedirect('/cursos');

        $this->assertSoftDeleted($course);
        $this->actingAs($course->ponente)->get("/cursos/{$course->id}/editar")->assertNotFound();
    }
}
