<?php

// Solo las reglas que usa la app hasta ahora; se agregan conforme se necesiten.
return [
    'required' => 'El campo :attribute es obligatorio.',
    'string' => 'El campo :attribute debe ser texto.',
    'email' => 'El campo :attribute debe ser un correo válido.',
    'max' => [
        'string' => 'El campo :attribute no debe tener más de :max caracteres.',
        'file' => 'El archivo :attribute no debe pesar más de :max KB.',
    ],
    'min' => ['string' => 'El campo :attribute debe tener al menos :min caracteres.'],
    'unique' => 'Ese :attribute ya está registrado.',
    'confirmed' => 'La confirmación de :attribute no coincide.',
    'file' => 'El campo :attribute debe ser un archivo.',
    'uploaded' => 'No se pudo subir :attribute (¿pesa más de 2 MB?).',
    'mimes' => 'El archivo :attribute debe ser de tipo: :values.',
    'dimensions' => 'La imagen :attribute es demasiado grande (máximo 3000 × 3000 px).',
    'regex' => 'El formato de :attribute no es válido.',
    'url' => 'El campo :attribute debe ser un enlace válido que empiece con https://.',

    'attributes' => [
        'name' => 'nombre',
        'email' => 'correo',
        'password' => 'contraseña',
        'avatar' => 'foto de perfil',
        'bio' => 'sobre ti',
        'interests' => 'intereses',
        'accent_color' => 'color de acento',
        'headline' => 'título',
        'website_url' => 'sitio web',
    ],
];
