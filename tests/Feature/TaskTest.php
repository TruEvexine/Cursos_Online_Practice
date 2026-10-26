<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class TaskTest extends TestCase
{
    use DatabaseTransactions;

    private function topic(): array
    {
        $course = Course::factory()->create();
        $topic = $course->blocks()->create(['title' => 'B', 'position' => 1])->topics()->create(['title' => 'Tema', 'position' => 1, 'is_required' => true]);

        return [$course, $topic];
    }

    private function payload(array $extra = []): array
    {
        return array_merge(['title' => 'Ensayo', 'instructions' => '**Dos** cuartillas', 'due_at' => '2026-11-10T23:59', 'max_score' => 10, 'weight' => 2, 'is_required' => '1', 'allow_late' => '0', 'position' => 1], $extra);
    }

    public function test_owner_creates_edits_and_deletes_a_task_with_due_date_in_mexico_city_time(): void
    {
        [$course, $topic] = $this->topic();
        $this->actingAs($course->ponente);

        $this->get("/temas/{$topic->id}/tareas/crear")->assertOk();
        $this->post("/temas/{$topic->id}/tareas", $this->payload())->assertRedirect("/temas/{$topic->id}/editar")->assertSessionHasNoErrors();

        $task = $topic->tasks()->sole();
        $this->assertSame('2026-11-11 05:59:00', $task->due_at->utc()->format('Y-m-d H:i:s'));
        $this->assertSame(2.0, (float) $task->weight);
        $this->get("/temas/{$topic->id}/editar")->assertSee('Ensayo')->assertSee('Vence el 10 nov. 2026, 23:59');

        $this->put("/tareas/{$task->id}", $this->payload(['title' => 'Ensayo final', 'due_at' => '', 'allow_late' => '1']))->assertSessionHasNoErrors();
        $this->assertNull($task->fresh()->due_at);
        $this->assertTrue($task->fresh()->allow_late);

        $student = User::factory()->role('alumno')->create();
        (new Submission(['body' => 'x']))->forceFill(['task_id' => $task->id, 'user_id' => $student->id])->save();
        $this->get("/tareas/{$task->id}/editar")->assertSee('Se borrarán también su entrega');
        $this->delete("/tareas/{$task->id}")->assertRedirect("/temas/{$topic->id}/editar");
        $this->assertModelMissing($task);
    }

    public function test_validation(): void
    {
        [$course, $topic] = $this->topic();

        $this->actingAs($course->ponente)->post("/temas/{$topic->id}/tareas", $this->payload(['title' => '', 'max_score' => 0, 'due_at' => 'mañana', 'weight' => -1]))
            ->assertSessionHasErrors(['title', 'max_score', 'due_at', 'weight']);
    }

    public function test_other_ponente_director_and_alumno_get_403(): void
    {
        [$course, $topic] = $this->topic();
        $task = $topic->tasks()->create(['title' => 'T', 'max_score' => 10]);

        foreach ([Course::factory()->create()->ponente, User::factory()->role('director')->create(), User::factory()->role('alumno')->create()] as $user) {
            $this->actingAs($user);
            $this->get("/temas/{$topic->id}/tareas/crear")->assertForbidden();
            $this->post("/temas/{$topic->id}/tareas", $this->payload())->assertForbidden();
            $this->get("/tareas/{$task->id}/editar")->assertForbidden();
            $this->put("/tareas/{$task->id}", $this->payload(['title' => 'Hackeada']))->assertForbidden();
            $this->delete("/tareas/{$task->id}")->assertForbidden();
        }
        $this->assertSame('T', $task->fresh()->title);
        $this->assertSame(1, $topic->tasks()->count());
    }
}
