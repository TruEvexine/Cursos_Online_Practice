<?php

namespace Tests\Feature;

use App\Http\Controllers\ReaderController;
use App\Models\Course;
use App\Models\Submission;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class GradesTest extends TestCase
{
    use DatabaseTransactions;

    private User $student;

    private Course $course;

    private $topic;

    protected function setUp(): void
    {
        parent::setUp();
        $this->student = User::factory()->role('alumno')->create();
        $this->course = Course::factory()->enroll($this->student)->create(['unlock_mode' => 'free']);
        $this->topic = $this->course->blocks()->create(['title' => 'Unidad 1', 'position' => 1])->topics()->create(['title' => 'Tema', 'position' => 1, 'is_required' => true]);
    }

    private function task(string $title, array $attrs = []): Task
    {
        return $this->topic->tasks()->create(array_merge(['title' => $title, 'max_score' => 10, 'weight' => 1], $attrs));
    }

    private function submission(Task $task, string $status, ?float $score = null, ?string $feedback = null): Submission
    {
        $s = (new Submission(['body' => 'x']))->forceFill(['task_id' => $task->id, 'user_id' => $this->student->id, 'status' => $status, 'score' => $score, 'feedback' => $feedback]);
        $s->save();

        return $s;
    }

    public function test_weighted_average_counts_only_graded_work(): void
    {
        $this->submission($this->task('A', ['weight' => 1]), 'graded', 10);          // 100 %
        $this->submission($this->task('B', ['weight' => 3, 'max_score' => 20]), 'graded', 10); // 50 %
        $this->submission($this->task('C', ['weight' => 5]), 'submitted');
        $this->submission($this->task('D', ['weight' => 0]), 'graded', 0);          // peso 0: no cuenta

        $subs = Submission::where('user_id', $this->student->id)->with('task')->get();
        $this->assertSame(62.5, ReaderController::weightedAverage($subs)); // (1×100 + 3×50) / 4
        $this->assertNull(ReaderController::weightedAverage(collect()));

        $this->actingAs($this->student)->get("/cursos/{$this->course->id}/calificaciones")->assertOk()
            ->assertSee('62.5')->assertSee('Calificada: 10/20 · 50 % · por reforzar')->assertSee('Entregada, sin calificar');
    }

    public function test_pending_tasks_and_next_due(): void
    {
        $later = $this->task('Después', ['due_at' => now()->addDays(5)]);
        $soon = $this->task('Pronto', ['due_at' => now()->addDay()]);
        $this->task('Sin fecha');
        $this->task('Vencida', ['due_at' => now()->subDay()]);
        $this->task('Vencida pero acepta tarde', ['due_at' => now()->subDay(), 'allow_late' => true]);
        $this->submission($this->task('Ya entregada', ['due_at' => now()->addDay()]), 'submitted');
        $this->submission($returned = $this->task('Devuelta', ['due_at' => now()->addDays(2)]), 'returned', null, 'Corrige');

        $titles = $this->course->pendingTasksFor($this->student)->pluck('title')->all();
        $this->assertSame(['Vencida pero acepta tarde', 'Pronto', 'Devuelta', 'Después', 'Sin fecha'], $titles);

        $this->actingAs($this->student)->get('/alumno')->assertSee('Próxima entrega:')->assertSee('Vencida pero acepta tarde')->assertSee('5 pendientes');
        $this->get("/cursos/{$this->course->id}")->assertSee('Próxima entrega')->assertSee('Mis calificaciones');
    }

    public function test_grades_page_is_only_for_the_student(): void
    {
        foreach ([$this->course->ponente, User::factory()->role('director')->create()] as $user) {
            $this->actingAs($user)->get("/cursos/{$this->course->id}/calificaciones")->assertForbidden();
        }
        $this->actingAs(User::factory()->role('alumno')->create())->get("/cursos/{$this->course->id}/calificaciones")->assertForbidden();
    }
}
