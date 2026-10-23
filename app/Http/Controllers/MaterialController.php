<?php

namespace App\Http\Controllers;

use App\Http\Requests\MaterialRequest;
use App\Models\Course;
use App\Models\Material;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MaterialController extends Controller
{
    public function index(Course $course)
    {
        Gate::authorize('update', $course);
        $course->load(['blocks.topics', 'materials' => fn ($q) => $q->with('topic')->latest('id')]);

        return view('materials.index', ['course' => $course, 'maxMb' => Material::maxUploadMb()]);
    }

    public function store(MaterialRequest $request, Course $course)
    {
        $data = $request->safe()->only(['title', 'kind', 'topic_id', 'url']);

        if ($data['kind'] === 'file') {
            $file = $request->file('file');
            // Disco privado y nombre aleatorio; el nombre original solo se guarda para la descarga.
            $data += [
                'file_path' => $file->storeAs('materials', Str::random(40).'.'.$file->extension(), 'local'),
                'original_name' => Str::limit($file->getClientOriginalName(), 250, ''),
                'mime_type' => $file->getMimeType(),
                'size_bytes' => $file->getSize(),
            ];
        }

        $material = $course->materials()->make($data);
        $material->uploaded_by = $request->user()->id;
        $material->save();

        return redirect()->route('materials.index', $course)->with('status', 'Material agregado.');
    }

    public function destroy(Material $material)
    {
        $course = $material->course ?? abort(404);
        Gate::authorize('update', $course);

        $material->delete();
        if ($material->file_path) {
            Storage::disk('local')->delete($material->file_path);
        }

        return redirect()->route('materials.index', $course)->with('status', 'Material borrado.');
    }

    /** Siempre como adjunto (nunca se muestra en el navegador) y solo para quien puede ver el curso. */
    public function download(Material $material)
    {
        Gate::authorize('view', $material->course ?? abort(404));
        abort_unless($material->kind === 'file' && Storage::disk('local')->exists($material->file_path), 404);

        return Storage::disk('local')->download($material->file_path, $material->original_name, ['Content-Type' => $material->mime_type]);
    }
}
