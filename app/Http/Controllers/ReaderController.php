<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Topic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

/**
 * Lectura del curso (alumno inscrito, director, y vista previa del dueño o super admin).
 * Solo lee: nunca escribe topic_progress ni entregas; en la vista previa eso debe seguir así en las Fases 3 y 4.
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
        $course->load(['ponente', 'blocks.topics', 'materials' => fn ($q) => $q->whereNull('topic_id')->orderBy('id')]);

        return view('reader.course', ['course' => $course]);
    }

    public function topic(Topic $topic)
    {
        $course = $topic->block->course ?? abort(404);
        Gate::authorize('view', $course);

        $ordered = $course->load('blocks.topics')->blocks->flatMap->topics->values();
        $i = $ordered->search(fn (Topic $t) => $t->id === $topic->id);

        return view('reader.topic', [
            'course' => $course,
            'topic' => $topic->load(['block', 'materials' => fn ($q) => $q->orderBy('id')]),
            'prev' => $i > 0 ? $ordered[$i - 1] : null,
            'next' => $ordered[$i + 1] ?? null,
        ]);
    }
}
