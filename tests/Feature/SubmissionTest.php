<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Submission;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use App\Http\Controllers\ReaderController;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\MessageBag;
use Illuminate\Support\ViewErrorBag;
use Tests\TestCase;

class SubmissionTest extends TestCase
{
    use DatabaseTransactions;

    private User $student;

    private Course $course;

    private Task $task;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        $this->student = User::factory()->role('alumno')->create();
        $this->course = Course::factory()->enroll($this->student)->create();
        $topic = $this->course->blocks()->create(['title' => 'B', 'position' => 1])->topics()->create(['title' => 'Tema', 'position' => 1, 'is_required' => true]);
        $this->task = $topic->tasks()->create(['title' => 'Ensayo', 'max_score' => 10, 'due_at' => now()->addDay()]);
    }

    private function submit(array $data = ['body' => 'Mi respuesta'], ?User $as = null)
    {
        return $this->actingAs($as ?? $this->student)->post("/tareas/{$this->task->id}/entrega", $data);
    }

    public function test_student_submits_and_edits_until_graded_keeping_the_first_submission_time(): void
    {
        $this->submit()->assertRedirect()->assertSessionHasNoErrors();
        $submission = Submission::sole();
        $this->assertSame('submitted', $submission->status);
        $first = $submission->submitted_at;

        $this->travel(2)->hours();
        $this->submit(['body' => 'Versión corregida'])->assertSessionHasNoErrors();
        $this->assertSame('Versión corregida', $submission->fresh()->body);
        $this->assertTrue($first->equalTo($submission->fresh()->submitted_at));
        $this->assertSame(1, Submission::count());

        $submission->forceFill(['status' => 'graded', 'score' => 9])->save();
        $this->submit(['body' => 'Otra'])->assertSessionHasErrors('body');
        $this->assertSame('Versión corregida', $submission->fresh()->body);

        $this->get("/temas/{$this->task->topic_id}")->assertSee('Calificada: 9/10 · 90 %')->assertDontSee('Guardar cambios');
    }

    public function test_deadline_rules_late_flag_and_returned_work(): void
    {
        $this->travel(2)->days();
        $this->submit()->assertSessionHasErrors(['body' => 'La fecha límite ya pasó y esta tarea no acepta entregas tarde.']);

        $this->task->update(['allow_late' => true]);
        $this->submit()->assertSessionHasNoErrors();
        $submission = Submission::sole();
        $this->assertTrue($submission->isLate());
        $this->get("/temas/{$this->task->topic_id}")->assertSee('Entregada tarde, sin calificar');

        // Devuelta: se puede volver a entregar aunque ya venció y no acepte tarde.
        $this->task->update(['allow_late' => false]);
        $submission->forceFill(['status' => 'returned', 'feedback' => 'Falta la conclusión.'])->save();
        $this->get("/temas/{$this->task->topic_id}")->assertSee('Devuelta: corrígela y vuelve a entregar')->assertSee('Falta la conclusión.');
        $this->submit(['body' => 'Con conclusión'])->assertSessionHasNoErrors();
        $this->assertSame('submitted', $submission->fresh()->status);
    }

    public function test_needs_text_or_file_and_files_follow_upload_rules(): void
    {
        $this->submit(['body' => ''])->assertSessionHasErrors('body');
        $this->submit(['file' => UploadedFile::fake()->create('virus.exe', 5, 'application/x-msdownload')])->assertSessionHasErrors('file');

        $this->submit(['file' => UploadedFile::fake()->create('ensayo.pdf', 50, 'application/pdf')])->assertSessionHasNoErrors();
        $submission = Submission::sole();
        $this->assertMatchesRegularExpression('#^submissions/[A-Za-z0-9]{40}\.pdf$#', $submission->file_path);
        $old = $submission->file_path;

        $this->submit(['file' => UploadedFile::fake()->create('ensayo-v2.pdf', 50, 'application/pdf')])->assertSessionHasNoErrors();
        Storage::disk('local')->assertMissing($old);

        $this->actingAs($this->student)->get("/entregas/{$submission->id}/archivo")->assertOk()->assertDownload('ensayo-v2.pdf');
        $this->actingAs($this->course->ponente)->get("/entregas/{$submission->id}/archivo")->assertOk();
        foreach ([User::factory()->role('alumno')->create(), Course::factory()->create()->ponente] as $other) {
            $this->actingAs($other)->get("/entregas/{$submission->id}/archivo")->assertForbidden();
        }
    }

    public function test_only_active_enrolled_students_with_the_topic_open_can_submit(): void
    {
        foreach ([$this->course->ponente, User::factory()->role('super_admin')->create(), User::factory()->role('alumno')->create()] as $user) {
            $this->submit(as: $user)->assertForbidden();
        }

        $this->course->enrollments()->update(['status' => 'completed']);
        $this->submit()->assertForbidden();
        $this->course->enrollments()->update(['status' => 'active']);

        // Tarea de un tema bloqueado.
        $locked = $this->course->blocks()->sole()->topics()->create(['title' => 'Después', 'position' => 2, 'is_required' => true]);
        $lockedTask = $locked->tasks()->create(['title' => 'Bloqueada', 'max_score' => 10]);
        $this->actingAs($this->student)->post("/tareas/{$lockedTask->id}/entrega", ['body' => 'x'])->assertForbidden();

        $this->assertSame(0, Submission::count());
    }

    public function test_preview_shows_tasks_without_the_form(): void
    {
        $this->actingAs($this->course->ponente)->get("/temas/{$this->task->topic_id}")->assertOk()
            ->assertSee('Ensayo')->assertDontSee('action="'.route('submissions.store', $this->task).'"', false);
    }

    public function test_an_error_shows_only_under_the_task_that_was_submitted(): void
    {
        $other = $this->task->topic->tasks()->create(['title' => 'Otra tarea', 'max_score' => 10]);
        $this->submit(['body' => '', 'task_id' => $this->task->id])->assertSessionHasErrors('body');

        // Lo que deja el redirect con errores: el error y lo escrito, con la tarea que se envió.
        // (Se renderiza la vista directo: en pruebas, el middleware vuelve a compartir los errores de la sesión en memoria.)
        $this->actingAs($this->student);
        session()->put('_old_input', ['task_id' => (string) $this->task->id, 'body' => 'borrador a medias']);
        request()->setLaravelSession(session()->driver());
        view()->share('errors', (new ViewErrorBag)->put('default', new MessageBag(['body' => ['Escribe tu respuesta o adjunta un archivo.']])));
        $html = app(ReaderController::class)->topic($this->task->topic)->render();

        $this->assertSame(1, substr_count($html, 'Escribe tu respuesta o adjunta un archivo.'));
        $this->assertStringContainsString('id="body-'.$this->task->id.'-error"', $html);
        $this->assertStringNotContainsString('id="body-'.$other->id.'-error"', $html);
        $this->assertSame(1, substr_count($html, 'borrador a medias'));
    }
}
