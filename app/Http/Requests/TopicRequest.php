<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TopicRequest extends FormRequest
{
    public function authorize(): bool
    {
        // El curso sale del bloque de la ruta (crear) o del propio tema (editar), nunca del cuerpo del request.
        $block = $this->route('block') ?? $this->route('topic')->block;

        return $block->course && $this->user()->can('update', $block->course);
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:160'],
            'content' => ['nullable', 'string', 'max:100000'],
            'position' => ['required', 'integer', 'min:0', 'max:65535'],
            'is_required' => ['required', 'boolean'],
        ];
    }
}
