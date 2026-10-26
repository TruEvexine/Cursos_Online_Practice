<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/** Fase 3, "hecho cuando": un alumno entrega, el ponente califica, el siguiente bloque se abre y la nota aparece en su vista. */
class Phase3AcceptanceTest extends TestCase
{
    use DatabaseTransactions;

    public function test_submit_grade_unlock_and_see_the_grade(): void
    {
        $student = User::factory()->role('alumno')->create();
        $course = Course::factory()->enroll($student)->create(['unlock_mode' => 'sequential']);
        $first = $course->blocks()->create(['title' => 'Bloque 1', 'position' => 1])->topics()->create(['title' => 'Tema 1', 'position' => 1, 'is_required' => true]);
        $next = $course->blocks()->create(['title' => 'Bloque 2', 'position' => 2])->topics()->create(['title' => 'Tema 2', 'position' => 1, 'is_required' => true]);
        $task = $first->tasks()->create(['title' => 'Ensayo', 'max_score' => 10, 'due_at' => now()->addDay()]);

        $this->actingAs($student)->get("/temas/{$next->id}")->assertForbidden();
        $this->post("/temas/{$first->id}/visto");
        $this->post("/tareas/{$task->id}/entrega", ['body' => 'Mi ensayo'])->assertSessionHasNoErrors();
        $this->get("/temas/{$next->id}")->assertOk();

        $submission = Submission::sole();
        $this->actingAs($course->ponente)->put("/entregas/{$submission->id}/calificar", ['score' => 9, 'feedback' => 'Muy bien.'])->assertRedirect();

        $this->actingAs($student)->get("/cursos/{$course->id}/calificaciones")->assertOk()
            ->assertSee('Calificada: 9/10 · 90 %')->assertSee('Muy bien.')->assertSee('90');
        $this->get("/temas/{$next->id}")->assertOk();
    }
}
