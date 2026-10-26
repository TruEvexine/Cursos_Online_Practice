<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class PonenteHomeTest extends TestCase
{
    use DatabaseTransactions;

    public function test_shows_own_courses_pending_counts_and_hides_other_ponentes(): void
    {
        $student = User::factory()->role('alumno')->create();
        $course = Course::factory()->enroll($student)->create(['title' => 'Mi curso con pendientes']);
        $task = $course->blocks()->create(['title' => 'B', 'position' => 1])->topics()->create(['title' => 'T', 'position' => 1, 'is_required' => true])
            ->tasks()->create(['title' => 'Tarea', 'max_score' => 10]);
        (new Submission(['body' => 'x']))->forceFill(['task_id' => $task->id, 'user_id' => $student->id, 'status' => 'submitted'])->save();
        $other = Course::factory()->create(['title' => 'Curso ajeno']);

        $this->actingAs($course->ponente)->get('/ponente')->assertOk()
            ->assertSee('Mi curso con pendientes')->assertSee('1 por calificar')->assertSee('1 alumno activo')
            ->assertSee('entrega espera')->assertSee(route('courses.create'), false)->assertDontSee('Curso ajeno');
    }

    public function test_empty_and_up_to_date_states_and_other_roles_get_403(): void
    {
        $ponente = User::factory()->role('ponente')->create();
        $this->actingAs($ponente)->get('/ponente')->assertOk()->assertSee('Estás al día')->assertSee('Todavía no tienes cursos')->assertSee('Crear un curso');

        foreach (['alumno', 'director'] as $role) {
            $this->actingAs(User::factory()->role($role)->create())->get('/ponente')->assertForbidden();
        }
    }
}
