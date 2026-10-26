<?php

namespace App\Http\Controllers;

use App\Http\Requests\SubmissionRequest;
use App\Models\Submission;
use App\Models\Task;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SubmissionController extends Controller
{
    /**
     * Entregar o editar la propia entrega. Se puede editar hasta que se califica; si el ponente la devuelve, otra vez.
     * Después de la fecha límite solo si la tarea acepta entregas tarde (o si fue devuelta).
     * submitted_at guarda la PRIMERA entrega (la racha de comodines y "tarde" se miden con ella).
     */
    public function store(SubmissionRequest $request, Task $task)
    {
        $submission = Submission::where('task_id', $task->id)->where('user_id', $request->user()->id)->first()
            ?? (new Submission)->forceFill(['task_id' => $task->id, 'user_id' => $request->user()->id]);

        if ($submission->status === 'graded') {
            throw ValidationException::withMessages(['body' => 'Esta entrega ya fue calificada; ya no se puede cambiar.']);
        }
        if ($task->isPastDue() && ! $task->allow_late && $submission->status !== 'returned') {
            throw ValidationException::withMessages(['body' => 'La fecha límite ya pasó y esta tarea no acepta entregas tarde.']);
        }
        if (! $request->filled('body') && ! $request->hasFile('file') && ! $submission->file_path) {
            throw ValidationException::withMessages(['body' => 'Escribe tu respuesta o adjunta un archivo.']);
        }

        $old = $submission->file_path;
        $submission->body = $request->validated('body');
        if ($file = $request->file('file')) {
            $submission->file_path = $file->storeAs('submissions', Str::random(40).'.'.$file->extension(), 'local');
            $submission->original_name = Str::limit($file->getClientOriginalName(), 250, '');
        }
        $submission->forceFill(['status' => 'submitted', 'score' => null, 'submitted_at' => $submission->submitted_at ?? now()])->save();

        if ($old && $old !== $submission->file_path) {
            Storage::disk('local')->delete($old);
        }

        return redirect()->to(route('reader.topic', $task->topic).'#tarea-'.$task->id)->with('status', 'Entrega guardada.');
    }

    /** Archivo de una entrega: su autor, quien califica el curso, director y super admin. */
    public function download(Submission $submission)
    {
        $course = $submission->task->topic->block->course ?? abort(404);
        $user = auth()->user();
        abort_unless($submission->user_id === $user->id || $user->can('update', $course) || $user->hasRole('director'), 403);
        abort_unless($submission->file_path && Storage::disk('local')->exists($submission->file_path), 404);

        return Storage::disk('local')->download($submission->file_path, $submission->original_name);
    }
}
