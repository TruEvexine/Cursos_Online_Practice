<?php

namespace App\Http\Controllers;

use App\Http\Requests\BlockRequest;
use App\Models\Block;
use App\Models\Course;
use Illuminate\Support\Facades\Gate;

/** Constructor del curso. Cada acción se autoriza con la Policy del curso dueño del bloque. */
class BlockController extends Controller
{
    public function index(Course $course)
    {
        Gate::authorize('update', $course);

        $course->load('blocks.topics');

        return view('blocks.index', ['course' => $course, 'nextPosition' => $course->blocks->max('position') + 1]);
    }

    public function store(BlockRequest $request, Course $course)
    {
        $course->blocks()->create($request->validated());

        return redirect()->route('blocks.index', $course)->with('status', 'Bloque agregado.');
    }

    public function edit(Block $block)
    {
        Gate::authorize('update', $block->course ?? abort(404));

        return view('blocks.edit', ['block' => $block->loadCount('topics')]);
    }

    public function update(BlockRequest $request, Block $block)
    {
        $block->update($request->validated());

        return redirect()->route('blocks.index', $block->course)->with('status', 'Bloque actualizado.');
    }

    public function destroy(Block $block)
    {
        $course = $block->course ?? abort(404);
        Gate::authorize('update', $course);

        $block->delete(); // Sus temas se borran en cascada (esquema).

        return redirect()->route('blocks.index', $course)->with('status', 'Bloque borrado.');
    }
}
