<?php

// Solo las reglas que usa la app hasta ahora; se agregan conforme se necesiten.
return [
    'required' => 'El campo :attribute es obligatorio.',
    'string' => 'El campo :attribute debe ser texto.',
    'email' => 'El campo :attribute debe ser un correo válido.',
    'max' => ['string' => 'El campo :attribute no debe tener más de :max caracteres.'],
    'min' => ['string' => 'El campo :attribute debe tener al menos :min caracteres.'],
    'unique' => 'Ese :attribute ya está registrado.',
    'confirmed' => 'La confirmación de :attribute no coincide.',

    'attributes' => [
        'name' => 'nombre',
        'email' => 'correo',
        'password' => 'contraseña',
    ],
];
