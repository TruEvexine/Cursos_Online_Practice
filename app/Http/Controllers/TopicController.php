<?php

namespace App\Http\Controllers;

use App\Http\Requests\TopicRequest;
use App\Models\Block;
use App\Models\Topic;
use Illuminate\Support\Facades\Gate;

/** Cada acción se autoriza con la Policy del curso dueño del bloque del tema. */
class TopicController extends Controller
{
    public function create(Block $block)
    {
        Gate::authorize('update', $block->course ?? abort(404));

        $topic = new Topic(['position' => $block->topics()->reorder()->max('position') + 1, 'is_required' => true]);

        return view('topics.form', ['block' => $block, 'topic' => $topic]);
    }

    public function store(TopicRequest $request, Block $block)
    {
        $block->topics()->create($request->validated());

        return redirect()->route('blocks.index', $block->course)->with('status', 'Tema agregado.');
    }

    public function edit(Topic $topic)
    {
        Gate::authorize('update', $topic->block->course ?? abort(404));

        return view('topics.form', ['block' => $topic->block, 'topic' => $topic]);
    }

    public function update(TopicRequest $request, Topic $topic)
    {
        $topic->update($request->validated());

        return redirect()->route('blocks.index', $topic->block->course)->with('status', 'Tema actualizado.');
    }

    public function destroy(Topic $topic)
    {
        $course = $topic->block->course ?? abort(404);
        Gate::authorize('update', $course);

        $topic->delete();

        return redirect()->route('blocks.index', $course)->with('status', 'Tema borrado.');
    }
}
