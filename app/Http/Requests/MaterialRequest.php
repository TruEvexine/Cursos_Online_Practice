<?php

namespace App\Http\Requests;

use App\Models\Material;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MaterialRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('course'));
    }

    public function rules(): array
    {
        $types = 'pdf,docx,pptx,xlsx,png,jpg,jpeg,webp';

        return [
            'title' => ['required', 'string', 'max:160'],
            'kind' => ['required', Rule::in(['file', 'link', 'video'])],
            // El tema tiene que ser de este mismo curso.
            'topic_id' => ['nullable', 'integer', Rule::exists('topics', 'id')->whereIn('block_id', $this->route('course')->blocks()->pluck('id'))],
            // Tipo por contenido (mimes) y por extensión del nombre (extensions): sin html, svg, zip ni ejecutables.
            'file' => ['exclude_unless:kind,file', 'required', 'file', 'max:'.Material::MAX_KB, "mimes:$types", "extensions:$types"],
            'url' => ['exclude_if:kind,file', 'required', 'url:https', 'max:500'],
        ];
    }

    public function messages(): array
    {
        $types = 'Solo se aceptan PDF, Word (.docx), PowerPoint (.pptx), Excel (.xlsx) o imágenes PNG, JPG y WebP.';

        return [
            'file.required' => 'Elige el archivo que quieres subir.',
            'file.uploaded' => 'El archivo no se pudo subir: pesa más de '.Material::maxUploadMb().' MB, el máximo que acepta el servidor.',
            'file.max' => 'El archivo pesa más de '.Material::maxUploadMb().' MB.',
            'file.mimes' => $types,
            'file.extensions' => $types,
            'url.required' => 'Pega el enlace (empieza con https://).',
        ];
    }
}
