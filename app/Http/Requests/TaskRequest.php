<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;

class TaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        // El curso sale del tema de la ruta (crear) o de la propia tarea (editar), nunca del cuerpo del request.
        $topic = $this->route('topic') ?? $this->route('task')->topic;

        return $topic->block->course && $this->user()->can('update', $topic->block->course);
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:160'],
            'instructions' => ['nullable', 'string', 'max:20000'],
            'due_at' => ['nullable', 'date'],
            'max_score' => ['required', 'numeric', 'min:1', 'max:999'],
            'weight' => ['required', 'numeric', 'min:0', 'max:999'],
            'is_required' => ['required', 'boolean'],
            'allow_late' => ['required', 'boolean'],
            'position' => ['required', 'integer', 'min:0', 'max:65535'],
        ];
    }

    /** La fecha límite se escribe en hora de Ciudad de México y se guarda en UTC. */
    public function taskData(): array
    {
        $data = $this->validated();
        $data['due_at'] = $data['due_at'] ? Carbon::parse($data['due_at'], 'America/Mexico_City')->utc() : null;

        return $data;
    }
}
