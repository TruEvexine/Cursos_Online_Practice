<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Submission;
use App\Models\Topic;
use App\Support\CourseProgress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

/**
 * Lectura del curso (alumno inscrito, director, y vista previa del dueño o super admin).
 * Solo markSeen escribe topic_progress, y solo para un alumno activo: la vista previa nunca escribe progreso ni entregas.
 */
class ReaderController extends Controller
{
    /** "Mis cursos": cursos publicados con inscripción 'active' o 'completed'. */
    public function myCourses(Request $request)
    {
        $enrollments = Enrollment::where('user_id', $request->user()->id)->whereIn('status', ['active', 'completed'])
            ->whereHas('course', fn ($q) => $q->where('status', 'published'))
            ->with('course.ponente')->orderBy('status')->orderByDesc('enrolled_at')->get();

        return view('reader.my-courses', ['enrollments' => $enrollments]);
    }

    public function course(Course $course)
    {
        Gate::authorize('view', $course);
        $course->load(['ponente', 'blocks.topics.tasks', 'materials' => fn ($q) => $q->whereNull('topic_id')->orderBy('id')]);

        return view('reader.course', ['course' => $course, 'progress' => $this->progress($course)]);
    }

    public function topic(Topic $topic)
    {
        $course = $topic->block->course ?? abort(404);
        Gate::authorize('view', $course);

        $progress = $this->progress($course);
        if (! $progress->isOpen($topic)) {
            abort(403, 'Este tema todavía está bloqueado. Completa «'.$progress->blocker($topic)?->title.'» para desbloquearlo.');
        }

        $ordered = $course->blocks->flatMap->topics->values();
        $i = $ordered->search(fn (Topic $t) => $t->id === $topic->id);

        return view('reader.topic', [
            'course' => $course,
            'topic' => $topic->load(['block', 'tasks', 'materials' => fn ($q) => $q->orderBy('id')]),
            'submissions' => $progress->student
                ? Submission::where('user_id', $progress->student->id)->whereIn('task_id', $topic->tasks->pluck('id'))->get()->keyBy('task_id')
                : collect(),
            'progress' => $progress,
            'prev' => $i > 0 ? $ordered[$i - 1] : null,
            'next' => $ordered[$i + 1] ?? null,
        ]);
    }

    /** "Marcar como visto": solo el alumno activo y con el tema abierto; la vista previa nunca llega aquí. */
    public function markSeen(Request $request, Topic $topic)
    {
        $course = $topic->block->course ?? abort(404);
        Gate::authorize('participate', $course);
        abort_unless($this->progress($course)->isOpen($topic), 403);

        DB::table('topic_progress')->insertOrIgnore(['user_id' => $request->user()->id, 'topic_id' => $topic->id, 'completed_at' => now()]);

        return redirect()->route('reader.topic', $topic)->with('status', 'Tema marcado como visto.');
    }

    /** El progreso solo existe para alumnos; dueño, super admin y director ven todo abierto. */
    private function progress(Course $course): CourseProgress
    {
        $user = auth()->user();

        return new CourseProgress($course, $user->hasRole('alumno') ? $user : null);
    }
}
