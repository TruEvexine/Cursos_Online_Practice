<?php

namespace App\Http\Requests;

use App\Models\Material;
use App\Support\CourseProgress;
use Illuminate\Foundation\Http\FormRequest;

class SubmissionRequest extends FormRequest
{
    /** Solo un alumno activo, con el tema de la tarea desbloqueado. */
    public function authorize(): bool
    {
        $task = $this->route('task');
        $course = $task->topic->block->course;

        return $course && $this->user()->can('participate', $course)
            && (new CourseProgress($course, $this->user()))->isOpen($task->topic);
    }

    public function rules(): array
    {
        $types = 'pdf,docx,pptx,xlsx,png,jpg,jpeg,webp';

        return [
            'body' => ['nullable', 'string', 'max:20000'],
            'file' => ['nullable', 'file', 'max:'.Material::MAX_KB, "mimes:$types", "extensions:$types"],
        ];
    }

    public function messages(): array
    {
        $types = 'Solo se aceptan PDF, Word (.docx), PowerPoint (.pptx), Excel (.xlsx) o imágenes PNG, JPG y WebP.';

        return [
            'file.uploaded' => 'El archivo no se pudo subir: pesa más de '.Material::maxUploadMb().' MB, el máximo que acepta el servidor.',
            'file.max' => 'El archivo pesa más de '.Material::maxUploadMb().' MB.',
            'file.mimes' => $types,
            'file.extensions' => $types,
        ];
    }
}
