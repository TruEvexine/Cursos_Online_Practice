<?php

namespace App\Http\Requests;

use App\Models\Course;
use App\Models\Role;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** Crear y editar curso. El ponente siempre es el usuario en sesión; solo el super admin elige ponente al crear. */
class CourseRequest extends FormRequest
{
    public function authorize(): bool
    {
        $course = $this->route('course');

        return $course ? $this->user()->can('update', $course) : $this->user()->can('create', Course::class);
    }

    public function rules(): array
    {
        $rules = [
            'title' => ['required', 'string', 'max:160'],
            'summary' => ['nullable', 'string', 'max:300'],
            'description' => ['nullable', 'string', 'max:10000'],
            'status' => ['required', Rule::in(['draft', 'published', 'archived'])],
            'starts_on' => ['nullable', 'date'],
            'ends_on' => ['nullable', 'date', 'after_or_equal:starts_on'],
        ];

        if (! $this->route('course') && $this->user()->hasRole('super_admin')) {
            $rules['ponente_id'] = ['required', Rule::exists('users', 'id')
                ->where('role_id', Role::where('slug', 'ponente')->value('id'))
                ->where('is_active', true)
                ->whereNull('deleted_at')];
        }

        return $rules;
    }
}
