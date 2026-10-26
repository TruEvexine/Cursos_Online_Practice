<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

/**
 * Catálogo público e inscripción por cuenta propia (hito DEMO, subconjunto de la Fase 8c).
 * Los cursos de pago usan un PAGO SIMULADO (modo prueba): no se cobra nada; solo se crea la inscripción y queda en la bitácora.
 */
class CatalogController extends Controller
{
    public function index()
    {
        $courses = Course::where('status', 'published')->whereNotNull('catalog_price')
            ->with('ponente')->withCount('blocks')->orderBy('title')->paginate(12);

        return view('catalog.index', ['courses' => $courses]);
    }

    public function show(Request $request, Course $course)
    {
        abort_unless($course->isListed(), 404);
        $course->load(['ponente', 'blocks.topics']);
        $enrollment = $request->user() ? $course->enrollments()->where('user_id', $request->user()->id)->first() : null;

        return view('catalog.show', ['course' => $course, 'enrollment' => $enrollment]);
    }

    public function checkout(Request $request, Course $course)
    {
        Gate::authorize('enrollSelf', $course);
        if ($redirect = $this->alreadyIn($request, $course)) {
            return $redirect;
        }
        abort_unless($course->catalog_price > 0, 404);

        return view('catalog.checkout', ['course' => $course]);
    }

    public function enroll(Request $request, Course $course)
    {
        Gate::authorize('enrollSelf', $course);
        if ($redirect = $this->alreadyIn($request, $course)) {
            return $redirect;
        }

        $enrollment = $course->enrollments()->create(['user_id' => $request->user()->id, 'status' => 'active', 'enrolled_at' => now()]);
        AuditLog::record($course->catalog_price > 0 ? 'enrollment.simulated_payment' : 'enrollment.self', $enrollment,
            ['course_id' => $course->id, 'amount' => (float) $course->catalog_price, 'simulated' => $course->catalog_price > 0]);

        return redirect()->route('reader.course', $course)->with('status', $course->catalog_price > 0
            ? 'Pago simulado registrado (modo prueba). Ya estás inscrito.'
            : 'Ya estás inscrito.');
    }

    private function alreadyIn(Request $request, Course $course)
    {
        return $course->hasReader($request->user()) ? redirect()->route('reader.course', $course) : null;
    }
}
