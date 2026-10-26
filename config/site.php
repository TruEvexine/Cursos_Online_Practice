<?php

// Portada pública. Cifras de EJEMPLO en MXN: en pantalla se rotulan "Ejemplo, sujetas a cambio".
return [

    'contact_email' => env('SITE_CONTACT_EMAIL'),
    'whatsapp' => env('SITE_WHATSAPP'), // solo dígitos con código de país, p. ej. 52XXXXXXXXXX

    // Registro abierto: versión de términos y aviso que se acepta (queda en audit_logs).
    'open_registration' => (bool) env('SITE_OPEN_REGISTRATION', false),
    'terms_version' => '2026-10-provisional',

    // Calificaciones: debajo de este porcentaje la nota se marca "por reforzar".
    'reinforce_below_percent' => 70,

    'plans' => [
        ['name' => 'Aprendiz', 'price' => 0, 'for' => 'Personas que aprenden por su cuenta.',
            'includes' => 'Entrar a los cursos a los que te inscriban. En los cursos de pago, el precio lo fija cada ponente.'],
        ['name' => 'Ponente', 'price' => 299, 'for' => 'Ponentes independientes que empiezan.',
            'includes' => 'Hasta 5 cursos y 150 alumnos activos.'],
        ['name' => 'Ponente Pro', 'price' => 699, 'for' => 'Ponentes con varios grupos.',
            'includes' => 'Cursos ilimitados y hasta 600 alumnos activos.'],
        ['name' => 'Escuela', 'price' => 1500, 'for' => 'Escuelas que vinculan a sus profesores y alumnos.',
            'includes' => 'Incluye 3 ponentes y 100 alumnos; $15 por alumno extra y $250 por ponente extra.'],
        ['name' => 'Institución a medida', 'price' => null, 'for' => 'Prepas, universidades e instituciones.',
            'includes' => 'Convenio por contacto, según el tamaño y lo que necesiten.'],
    ],

    // Simulador del plan Escuela (App\Support\SchoolQuote).
    'school' => [
        'base' => 1500,
        'included_students' => 100,
        'included_teachers' => 3,
        'per_extra_student' => 15,
        'per_extra_teacher' => 250,
        'custom_above_students' => 1000,
        'custom_above_teachers' => 30,
    ],

];
