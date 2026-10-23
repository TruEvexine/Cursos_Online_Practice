<?php

namespace App\Http\Controllers;

use App\Http\Requests\CourseRequest;
use App\Models\Course;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class CourseController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('viewAny', Course::class);
        $user = $request->user();

        $courses = Course::query()
            ->when($user->hasRole('ponente'), fn ($query) => $query->where('ponente_id', $user->id))
            ->with('ponente')
            ->withCount(['enrollments as students_count' => fn ($query) => $query->where('status', 'active')])
            ->latest('updated_at')
            ->paginate(20);

        return view('courses.index', ['courses' => $courses]);
    }

    public function create(Request $request)
    {
        Gate::authorize('create', Course::class);

        $ponentes = $request->user()->hasRole('super_admin')
            ? User::where('role_id', Role::where('slug', 'ponente')->value('id'))->where('is_active', true)->orderBy('name')->get(['id', 'name', 'email'])
            : collect();

        return view('courses.form', ['course' => new Course(['status' => 'draft']), 'ponentes' => $ponentes]);
    }

    public function store(CourseRequest $request)
    {
        $course = new Course($request->safe()->except('ponente_id'));
        $course->ponente_id = $request->user()->hasRole('ponente') ? $request->user()->id : $request->validated('ponente_id');
        $course->save();

        return redirect()->route('blocks.index', $course)->with('status', 'Curso creado. Ahora agrega su primer bloque.');
    }

    public function edit(Course $course)
    {
        Gate::authorize('update', $course);

        return view('courses.form', ['course' => $course, 'ponentes' => collect()]);
    }

    public function update(CourseRequest $request, Course $course)
    {
        $course->update($request->validated());

        return redirect()->route('courses.edit', $course)->with('status', 'Curso actualizado.');
    }

    public function destroy(Course $course)
    {
        Gate::authorize('delete', $course);

        // Alumnos activos o que ya lo terminaron conservan su historial: el curso se archiva, no se borra.
        if ($course->enrollments()->whereIn('status', ['active', 'completed'])->exists()) {
            return back()->withErrors(['delete' => 'Este curso tiene alumnos inscritos o que ya lo terminaron, así que no se puede borrar. Archívalo: cambia su estado a «Archivado» y guarda.']);
        }

        $course->delete();

        return redirect()->route('courses.index')->with('status', 'Curso borrado.');
    }
}
