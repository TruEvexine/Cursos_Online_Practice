<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Course;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class GradingTest extends TestCase
{
    use DatabaseTransactions;

    private Course $course;

    private Submission $submission;

    protected function setUp(): void
    {
        parent::setUp();
        $student = User::factory()->role('alumno')->create(['name' => 'Ana Torres']);
        $this->course = Course::factory()->enroll($student)->create();
        $task = $this->course->blocks()->create(['title' => 'B', 'position' => 1])->topics()->create(['title' => 'Tema', 'position' => 1, 'is_required' => true])
            ->tasks()->create(['title' => 'Ensayo', 'max_score' => 10]);
        $this->submission = (new Submission(['body' => 'Mi ensayo']))->forceFill(['task_id' => $task->id, 'user_id' => $student->id]);
        $this->submission->save();
    }

    public function test_owner_sees_inbox_grades_with_feedback_and_it_is_audited(): void
    {
        $this->actingAs($this->course->ponente);
        $this->get("/cursos/{$this->course->id}/entregas")->assertOk()->assertSee('Ana Torres')->assertSee('Ensayo')
            ->assertSee('1<span class="sr-only"> por calificar</span>', false);
        $this->get("/entregas/{$this->submission->id}")->assertOk()->assertSee('Mi ensayo');

        $this->put("/entregas/{$this->submission->id}/calificar", ['score' => 11])->assertSessionHasErrors('score');
        $this->put("/entregas/{$this->submission->id}/calificar", ['score' => 6.5, 'feedback' => 'Buen inicio; profundiza.'])
            ->assertRedirect("/cursos/{$this->course->id}/entregas");

        $s = $this->submission->fresh();
        $this->assertSame('graded', $s->status);
        $this->assertSame(6.5, (float) $s->score);
        $this->assertSame($this->course->ponente_id, $s->graded_by);
        $this->assertTrue($s->needsReinforcement()); // 65 % < 70 %
        $log = AuditLog::where('action', 'submission.graded')->where('entity_id', $s->id)->sole();
        $this->assertSame(6.5, $log->meta['score']);

        $this->get("/cursos/{$this->course->id}/entregas")->assertSee('No hay entregas pendientes.');
        $this->actingAs($s->user)->get("/temas/{$s->task->topic_id}")
            ->assertSee('Calificada: 6.5/10 · 65 % · por reforzar')->assertSee('Buen inicio; profundiza.');
    }

    public function test_return_requires_feedback_and_is_audited(): void
    {
        $this->actingAs($this->course->ponente);
        $this->put("/entregas/{$this->submission->id}/devolver", ['feedback' => ''])->assertSessionHasErrors('feedback');
        $this->put("/entregas/{$this->submission->id}/devolver", ['feedback' => 'Falta bibliografía.'])->assertRedirect();

        $this->assertSame('returned', $this->submission->fresh()->status);
        $this->assertTrue(AuditLog::where('action', 'submission.returned')->where('entity_id', $this->submission->id)->exists());
        $this->put("/entregas/{$this->submission->id}/calificar", ['score' => 9])->assertSessionHasErrors('score');
    }

    public function test_only_owner_or_super_admin_grade(): void
    {
        foreach ([Course::factory()->create()->ponente, User::factory()->role('director')->create(), $this->submission->user] as $user) {
            $this->actingAs($user);
            $this->get("/cursos/{$this->course->id}/entregas")->assertForbidden();
            $this->get("/entregas/{$this->submission->id}")->assertForbidden();
            $this->put("/entregas/{$this->submission->id}/calificar", ['score' => 10])->assertForbidden();
            $this->put("/entregas/{$this->submission->id}/devolver", ['feedback' => 'x'])->assertForbidden();
        }
        $this->assertSame('submitted', $this->submission->fresh()->status);

        $this->actingAs(User::factory()->role('super_admin')->create())->put("/entregas/{$this->submission->id}/calificar", ['score' => 10])->assertRedirect();
        $this->assertSame('graded', $this->submission->fresh()->status);
    }
}
