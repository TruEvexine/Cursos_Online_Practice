<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Course;
use App\Models\Submission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

/** Bandeja de entregas y calificación. Solo el ponente dueño y el super admin (CoursePolicy::update). */
class GradingController extends Controller
{
    public function index(Course $course)
    {
        Gate::authorize('update', $course);

        $pending = Submission::where('status', 'submitted')
            ->whereHas('task.topic.block', fn ($q) => $q->where('course_id', $course->id))
            ->with(['user', 'task.topic'])->orderBy('updated_at')->get();

        return view('grading.index', ['course' => $course, 'pending' => $pending]);
    }

    public function show(Submission $submission)
    {
        Gate::authorize('update', $this->course($submission));

        return view('grading.show', ['submission' => $submission->load(['user', 'task.topic.block.course'])]);
    }

    public function grade(Request $request, Submission $submission)
    {
        $course = $this->course($submission);
        Gate::authorize('update', $course);
        if ($submission->status === 'returned') {
            throw ValidationException::withMessages(['score' => 'Esta entrega está devuelta: espera a que el alumno la vuelva a entregar.']);
        }

        $data = $request->validate([
            'score' => ['required', 'numeric', 'min:0', 'max:'.$submission->task->max_score],
            'feedback' => ['nullable', 'string', 'max:5000'],
        ]);
        $submission->forceFill($data + ['status' => 'graded', 'graded_by' => $request->user()->id, 'graded_at' => now()])->save();
        AuditLog::record('submission.graded', $submission, ['course_id' => $course->id, 'task_id' => $submission->task_id, 'student_id' => $submission->user_id, 'score' => (float) $data['score']]);

        return redirect()->route('grading.index', $course)->with('status', 'Entrega calificada.');
    }

    /** Devolver = pedir que la rehaga; la retroalimentación es obligatoria para que sepa qué corregir. */
    public function returnToStudent(Request $request, Submission $submission)
    {
        $course = $this->course($submission);
        Gate::authorize('update', $course);

        $data = $request->validate(['feedback' => ['required', 'string', 'max:5000']]);
        $submission->forceFill($data + ['status' => 'returned', 'score' => null, 'graded_by' => $request->user()->id, 'graded_at' => now()])->save();
        AuditLog::record('submission.returned', $submission, ['course_id' => $course->id, 'task_id' => $submission->task_id, 'student_id' => $submission->user_id]);

        return redirect()->route('grading.index', $course)->with('status', 'Entrega devuelta al alumno.');
    }

    private function course(Submission $submission): Course
    {
        return $submission->task->topic->block->course ?? abort(404);
    }
}
