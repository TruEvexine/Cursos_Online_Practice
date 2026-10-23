<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BlockRequest extends FormRequest
{
    public function authorize(): bool
    {
        // El curso sale de la ruta (crear) o del propio bloque (editar), nunca del cuerpo del request.
        $course = $this->route('course') ?? $this->route('block')->course;

        return $course && $this->user()->can('update', $course);
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:160'],
            'description' => ['nullable', 'string', 'max:2000'],
            'position' => ['required', 'integer', 'min:0', 'max:65535'],
        ];
    }
}
