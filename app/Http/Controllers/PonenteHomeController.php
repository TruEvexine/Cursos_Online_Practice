<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Submission;
use Illuminate\Http\Request;

/** Inicio del ponente (DESIGN.md §4): sus cursos, entregas por calificar y acceso rápido a "Nuevo curso". */
class PonenteHomeController extends Controller
{
    public function __invoke(Request $request)
    {
        $courses = Course::where('ponente_id', $request->user()->id)
            ->withCount(['enrollments as students_count' => fn ($q) => $q->where('status', 'active')])->latest('updated_at')->get();

        $pending = Submission::where('status', 'submitted')
            ->whereHas('task.topic.block', fn ($q) => $q->whereIn('course_id', $courses->pluck('id')))
            ->with('task.topic.block')->get()->countBy(fn ($s) => $s->task->topic->block->course_id);

        return view('ponente.home', ['courses' => $courses, 'pending' => $pending]);
    }
}
