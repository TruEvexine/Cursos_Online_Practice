<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('manageStudents', $this->route('course'));
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email', 'max:190'],
            'name' => ['nullable', 'string', 'max:120'],
        ];
    }
}
