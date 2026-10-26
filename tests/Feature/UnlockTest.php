<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Submission;
use App\Models\Task;
use App\Models\Topic;
use App\Models\User;
use App\Support\CourseProgress;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class UnlockTest extends TestCase
{
    use DatabaseTransactions;

    private User $student;

    private Course $course;

    /** @var array<string, Topic> */
    private array $t = [];

    private Task $task;

    protected function setUp(): void
    {
        parent::setUp();
        $this->student = User::factory()->role('alumno')->create();
        $this->course = Course::factory()->enroll($this->student)->create(['unlock_mode' => 'sequential']);
        $b1 = $this->course->blocks()->create(['title' => 'Bloque 1', 'position' => 1]);
        $b2 = $this->course->blocks()->create(['title' => 'Bloque 2', 'position' => 2]);
        $this->t['uno'] = $b1->topics()->create(['title' => 'Uno', 'position' => 1, 'is_required' => true]);
        $this->t['opcional'] = $b1->topics()->create(['title' => 'Opcional', 'position' => 2, 'is_required' => false]);
        $this->t['dos'] = $b2->topics()->create(['title' => 'Dos', 'position' => 1, 'is_required' => true]);
        $this->t['tres'] = $b2->topics()->create(['title' => 'Tres', 'position' => 2, 'is_required' => true]);
        $this->task = $this->t['uno']->tasks()->create(['title' => 'Tarea 1', 'max_score' => 10, 'is_required' => true]);
        $this->t['uno']->tasks()->create(['title' => 'Extra opcional', 'max_score' => 10, 'is_required' => false]);
    }

    private function states(?User $user = null): array
    {
        $p = new CourseProgress($this->course->fresh(), $user ?? $this->student);

        return array_map(fn (Topic $topic) => $p->state($topic), $this->t);
    }

    private function submit(string $status = 'submitted'): Submission
    {
        $s = new Submission(['body' => 'Mi respuesta']);
        $s->forceFill(['task_id' => $this->task->id, 'user_id' => $this->student->id, 'status' => $status])->save();

        return $s;
    }

    public function test_sequential_unlock_needs_seen_plus_required_tasks_and_returned_relocks(): void
    {
        $this->assertSame(['uno' => 'available', 'opcional' => 'locked', 'dos' => 'locked', 'tres' => 'locked'], $this->states());

        $this->actingAs($this->student)->post("/temas/{$this->t['uno']->id}/visto")->assertRedirect();
        $this->assertSame('in_progress', $this->states()['uno']);
        $this->get("/temas/{$this->t['dos']->id}")->assertForbidden()->assertSee('Completa «Uno» para desbloquearlo', false);

        $submission = $this->submit();
        // La tarea opcional no hace falta; el tema opcional no bloquea a los siguientes.
        $this->assertSame(['uno' => 'completed', 'opcional' => 'available', 'dos' => 'available', 'tres' => 'locked'], $this->states());
        $this->get("/temas/{$this->t['dos']->id}")->assertOk();
        $this->post("/temas/{$this->t['tres']->id}/visto")->assertForbidden();

        $submission->forceFill(['status' => 'returned'])->save();
        $this->assertSame('locked', $this->states()['dos']);

        $submission->forceFill(['status' => 'graded', 'score' => 8])->save();
        $this->assertSame('available', $this->states()['dos']);
    }

    public function test_free_mode_opens_everything_but_still_tracks_progress(): void
    {
        $this->course->update(['unlock_mode' => 'free']);

        $this->assertSame(['uno' => 'available', 'opcional' => 'available', 'dos' => 'available', 'tres' => 'available'], $this->states());
        $this->actingAs($this->student)->post("/temas/{$this->t['tres']->id}/visto")->assertRedirect();
        $this->assertSame('completed', $this->states()['tres']);
    }

    public function test_staff_see_everything_open_and_never_write_progress(): void
    {
        foreach ([$this->course->ponente, User::factory()->role('director')->create(), User::factory()->role('super_admin')->create()] as $user) {
            $open = new CourseProgress($this->course->fresh(), null);
            $this->assertTrue($open->isOpen($this->t['tres']));
            $this->actingAs($user)->get("/temas/{$this->t['tres']->id}")->assertOk();
            $this->actingAs($user)->post("/temas/{$this->t['uno']->id}/visto")->assertForbidden();
        }
        $this->assertSame(0, DB::table('topic_progress')->whereIn('topic_id', array_map(fn ($t) => $t->id, $this->t))->count());
    }

    public function test_completed_enrollment_reads_but_cannot_mark_seen(): void
    {
        $this->course->enrollments()->where('user_id', $this->student->id)->update(['status' => 'completed']);

        $this->actingAs($this->student)->get("/temas/{$this->t['uno']->id}")->assertOk()->assertDontSee('Marcar como visto');
        $this->post("/temas/{$this->t['uno']->id}/visto")->assertForbidden();
    }

    public function test_active_student_sees_mark_seen_button_once(): void
    {
        $this->actingAs($this->student)->get("/temas/{$this->t['uno']->id}")->assertOk()->assertSee('Marcar como visto')->assertSee('0 de 2 pasos');
        $this->post("/temas/{$this->t['uno']->id}/visto");
        $this->post("/temas/{$this->t['uno']->id}/visto");
        $this->assertSame(1, DB::table('topic_progress')->where('user_id', $this->student->id)->count());
        $this->get("/temas/{$this->t['uno']->id}")->assertDontSee('Marcar como visto')->assertSee('1 de 2 pasos');
    }

    public function test_mission_map_shows_states_in_text(): void
    {
        $this->actingAs($this->student)->post("/temas/{$this->t['uno']->id}/visto");
        $this->submit();

        $this->get("/cursos/{$this->course->id}")->assertOk()
            ->assertSee('Tu mapa de misiones')->assertSee('Completado')->assertSee('Disponible')
            ->assertSee('Bloqueado: completa «Dos» para desbloquearlo.', false)
            ->assertSee('1 de 1 temas obligatorios')->assertSee('0 de 2 temas obligatorios')
            ->assertDontSee(route('reader.topic', $this->t['tres']), false);
    }
}
