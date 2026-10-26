<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Support\SchoolQuote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LandingController extends Controller
{
    /** Portada pública. El simulador del plan Escuela llega por GET (?alumnos=&ponentes=), sin JavaScript. */
    public function __invoke(Request $request)
    {
        $quote = null;
        $simulator = Validator::make($request->only('alumnos', 'ponentes'), [
            'alumnos' => ['required', 'integer', 'min:0', 'max:5000'],
            'ponentes' => ['required', 'integer', 'min:0', 'max:5000'],
        ], [
            '*.required' => 'Escribe un número.',
            '*.integer' => 'Escribe un número entero.',
            '*.min' => 'Escribe un número de 0 a 5,000.',
            '*.max' => 'Escribe un número de 0 a 5,000.',
        ]);

        $asked = $request->hasAny(['alumnos', 'ponentes']);
        if ($asked && $simulator->passes()) {
            $quote = ['amount' => SchoolQuote::monthly((int) $request->alumnos, (int) $request->ponentes)];
        }

        // Hasta 3 cursos reales del catálogo, con su portada.
        $featured = Course::where('status', 'published')->whereNotNull('catalog_price')->with('ponente')->latest('updated_at')->take(3)->get();

        return view('public.home', [
            'featured' => $featured,
            'simulatorErrors' => $asked ? $simulator->errors() : null,
            'quote' => $quote,
        ]);
    }
}
