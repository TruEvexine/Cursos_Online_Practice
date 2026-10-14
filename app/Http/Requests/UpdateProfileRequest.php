<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Solo edita el perfil propio: la ruta no recibe id.
    }

    public function rules(): array
    {
        $rules = [
            // Sin SVG ni GIF; la imagen se recodifica a WebP en el servidor.
            'avatar' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:2048', 'dimensions:max_width=3000,max_height=3000'],
            'bio' => ['nullable', 'string', 'max:2000'],
            'interests' => ['nullable', 'string', 'max:255'],
            'accent_color' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        ];

        // Alumno: perfil limitado (foto, bio, intereses, color). Los demás roles: completo.
        if (! $this->user()->hasRole('alumno')) {
            $rules += [
                'name' => ['required', 'string', 'max:120'],
                'headline' => ['nullable', 'string', 'max:120'],
                'website_url' => ['nullable', 'url:https', 'max:255'],
            ];
        }

        return $rules;
    }
}
