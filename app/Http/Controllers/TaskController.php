<?php

namespace App\Http\Controllers;

use App\Http\Requests\TaskRequest;
use App\Models\Task;
use App\Models\Topic;
use Illuminate\Support\Facades\Gate;

/** Tareas de un tema. Cada acción se autoriza con la Policy del curso dueño del tema. */
class TaskController extends Controller
{
    public function create(Topic $topic)
    {
        Gate::authorize('update', $topic->block->course ?? abort(404));

        $task = new Task(['position' => $topic->tasks()->reorder()->max('position') + 1, 'max_score' => 10, 'weight' => 1, 'is_required' => true, 'allow_late' => false]);

        return view('tasks.form', ['topic' => $topic, 'task' => $task]);
    }

    public function store(TaskRequest $request, Topic $topic)
    {
        $topic->tasks()->create($request->taskData());

        return redirect()->route('topics.edit', $topic)->with('status', 'Tarea agregada.');
    }

    public function edit(Task $task)
    {
        Gate::authorize('update', $task->topic->block->course ?? abort(404));

        return view('tasks.form', ['topic' => $task->topic, 'task' => $task->loadCount('submissions')]);
    }

    public function update(TaskRequest $request, Task $task)
    {
        $task->update($request->taskData());

        return redirect()->route('topics.edit', $task->topic)->with('status', 'Tarea actualizada.');
    }

    public function destroy(Task $task)
    {
        Gate::authorize('update', $task->topic->block->course ?? abort(404));

        $task->delete(); // Las entregas se borran en cascada; el formulario avisa cuántas.

        return redirect()->route('topics.edit', $task->topic)->with('status', 'Tarea borrada.');
    }
}
