<?php

// Contenido de los 3 cursos de la DEMO (app:seed-demo). 'due' = días desde hoy para la fecha límite (negativo = ya venció).
return [
    [
        'slug' => 'demo-estadistica-descriptiva',
        'ponente' => 'ponente1',
        'title' => 'Estadística descriptiva para ciencias sociales',
        'summary' => 'Resume datos con tablas, medidas y gráficas, con ejemplos de encuestas reales.',
        'description' => "Un curso práctico para quien nunca ha llevado estadística.\nAprenderás a organizar datos, calcular medidas de tendencia central y de dispersión, y a elegir la gráfica correcta.",
        'price' => 0,
        'blocks' => [
            ['title' => 'Datos y variables', 'description' => 'Distinguir tipos de variables y escalas de medición.', 'topics' => [
                ['title' => '¿Qué es la estadística?', 'content' => "La **estadística** estudia cómo recolectar, organizar, analizar e interpretar datos para responder preguntas.\n\n## Dos ramas\n\n- **Descriptiva:** resume lo que ya observaste (tablas, promedios, gráficas).\n- **Inferencial:** usa una muestra para concluir algo sobre una población.\n\n> Antes de calcular nada, escribe la pregunta que quieres responder.\n\n## Población y muestra\n\nLa **población** es el conjunto completo que te interesa (todos los hogares de Michoacán). La **muestra** es la parte que de verdad observas (2,000 hogares encuestados).",
                    'tasks' => [['title' => 'Tu primera pregunta estadística', 'instructions' => "Escribe una pregunta que se pueda responder con datos de tu entorno (escuela, trabajo, colonia).\n\n1. ¿Cuál es la población?\n2. ¿Qué muestra podrías observar?\n3. ¿Qué datos registrarías?", 'max' => 10, 'due' => -12]]],
                ['title' => 'Variables cualitativas y cuantitativas', 'content' => "Una **variable** es una característica que cambia de un caso a otro.\n\n| Tipo | Ejemplos |\n|---|---|\n| Cualitativa nominal | color favorito, municipio |\n| Cualitativa ordinal | nivel de satisfacción (bajo, medio, alto) |\n| Cuantitativa discreta | número de hijos |\n| Cuantitativa continua | estatura, ingreso |\n\nLa escala decide qué operaciones tienen sentido: no puedes promediar municipios, pero sí estaturas.",
                    'tasks' => [['title' => 'Clasifica las variables de una encuesta', 'instructions' => "Toma 8 preguntas de una encuesta pública (por ejemplo, del INEGI) y clasifica cada variable según la tabla del tema. Explica en una línea por qué.", 'max' => 10, 'due' => -5]]],
            ]],
            ['title' => 'Medidas de tendencia central', 'description' => 'Calcular e interpretar media, mediana y moda, y saber cuándo usar cada una.', 'topics' => [
                ['title' => 'Media, mediana y moda', 'content' => "## Media\n\nSuma de los valores entre el número de valores. Es sensible a valores extremos.\n\n## Mediana\n\nEl valor que queda en medio al ordenar los datos. No le afectan los extremos.\n\n## Moda\n\nEl valor que más se repite. Es la única que sirve para variables nominales.\n\n**Ejemplo:** ingresos de 5 personas: 8, 9, 10, 11 y 95 mil pesos. La media es 26.6 mil; la mediana, 10 mil. ¿Cuál describe mejor al grupo?",
                    'tasks' => [['title' => 'Calcula y compara', 'instructions' => "Con los datos de tiempo de traslado de tu grupo (o inventa 15 valores realistas), calcula media, mediana y moda. ¿Cuál reportarías y por qué?", 'max' => 20, 'weight' => 2, 'due' => 3]]],
                ['title' => 'Lectura complementaria: el promedio engañoso', 'required' => false, 'content' => "Cuando una distribución es **asimétrica**, la media se va hacia la cola larga. Por eso los reportes de ingreso suelen usar la mediana.\n\nPregúntate siempre: *¿qué número cambiaría si quito el valor más extremo?*", 'tasks' => []],
            ]],
            ['title' => 'Gráficas que comunican', 'description' => 'Elegir la gráfica adecuada para cada tipo de variable.', 'topics' => [
                ['title' => 'Barras, histogramas y pasteles', 'content' => "- **Barras:** comparar categorías.\n- **Histograma:** ver la forma de una variable continua.\n- **Pastel:** solo si son pocas categorías que suman un todo.\n\nUna buena gráfica tiene título, ejes con unidades y fuente de los datos.",
                    'tasks' => [['title' => 'Rediseña una gráfica', 'instructions' => "Busca una gráfica confusa en un periódico o red social. Explica qué falla y propón una mejor (puedes adjuntar una imagen o un PDF).", 'max' => 10, 'due' => 14]]],
            ]],
        ],
        'materials' => [
            ['title' => 'Datos abiertos del INEGI', 'url' => 'https://www.inegi.org.mx/datos/'],
            ['title' => 'Guía rápida de medidas (PDF)', 'file' => 'Guía rápida: media, mediana y moda. Material de práctica de Gaining Grades.'],
        ],
    ],
    [
        'slug' => 'demo-python-desde-cero',
        'ponente' => 'ponente2',
        'title' => 'Programación en Python desde cero',
        'summary' => 'Variables, decisiones y ciclos con ejercicios cortos que puedes correr en tu navegador.',
        'description' => "Para personas que nunca han programado.\nCada tema trae un ejercicio pequeño; al final del curso harás un programa que resuelve un problema real.",
        'price' => 499,
        'blocks' => [
            ['title' => 'Primeros pasos', 'description' => 'Escribir y ejecutar tus primeros programas.', 'topics' => [
                ['title' => 'Tu primer programa', 'content' => "Python lee tus instrucciones de arriba hacia abajo.\n\n```\nprint(\"Hola, mundo\")\n```\n\nPuedes probar código sin instalar nada en un intérprete en línea, o instalar Python desde su sitio oficial.\n\n> Programar es escribir instrucciones tan claras que una computadora no pueda malinterpretarlas.",
                    'tasks' => [['title' => 'Hola con tu nombre', 'instructions' => "Escribe un programa que pida tu nombre con `input()` y salude usándolo. Pega el código y una captura del resultado.", 'max' => 10, 'due' => -9]]],
                ['title' => 'Variables y tipos', 'content' => "Una **variable** guarda un valor con un nombre.\n\n```\nedad = 20\nnombre = \"Ana\"\npromedio = 8.7\n```\n\n- `int`: enteros\n- `float`: decimales\n- `str`: texto\n- `bool`: `True` o `False`\n\nUsa `type(x)` para saber el tipo de una variable.",
                    'tasks' => [['title' => 'Calculadora de propina', 'instructions' => "Pide el total de la cuenta y el porcentaje de propina, y muestra cuánto dejar y el total a pagar con dos decimales.", 'max' => 10, 'due' => -2]]],
            ]],
            ['title' => 'Decisiones y repeticiones', 'description' => 'Usar if, for y while para que el programa decida y repita.', 'topics' => [
                ['title' => 'Condicionales', 'content' => "```\nif calificacion >= 6:\n    print(\"Aprobado\")\nelse:\n    print(\"Reprobado\")\n```\n\nLa **sangría** (espacios al inicio) indica qué líneas pertenecen al `if`. Python no usa llaves.",
                    'tasks' => [['title' => '¿Año bisiesto?', 'instructions' => "Escribe un programa que diga si un año es bisiesto: divisible entre 4, excepto los divisibles entre 100 que no lo son entre 400.", 'max' => 10, 'due' => 4]]],
                ['title' => 'Ciclos for y while', 'content' => "`for` recorre una secuencia; `while` repite mientras una condición se cumpla.\n\n```\nfor i in range(1, 6):\n    print(i)\n```\n\nCuidado con los ciclos infinitos: asegúrate de que la condición de un `while` llegue a ser falsa.",
                    'tasks' => [['title' => 'Tabla de multiplicar', 'instructions' => "Pide un número y muestra su tabla del 1 al 10. Extra: pide hasta qué número mostrarla.", 'max' => 10, 'due' => 10]]],
            ]],
        ],
        'materials' => [
            ['title' => 'Tutorial oficial de Python (español)', 'url' => 'https://docs.python.org/es/3/tutorial/'],
            ['title' => 'Hoja de referencia de sintaxis (PDF)', 'file' => 'Hoja de referencia de Python: print, input, if, for, while. Material de práctica de Gaining Grades.'],
        ],
    ],
    [
        'slug' => 'demo-redaccion-academica',
        'ponente' => 'ponente3',
        'title' => 'Redacción académica clara',
        'summary' => 'Escribe ensayos y reportes que se entienden a la primera, con citas bien hechas.',
        'description' => "Un taller de escritura para preparatoria y universidad.\nTrabajarás sobre tus propios textos: cada tarea es un borrador que tu ponente revisa con retroalimentación.",
        'price' => 299,
        'blocks' => [
            ['title' => 'La idea central', 'description' => 'Escribir una tesis clara y ordenar los argumentos que la sostienen.', 'topics' => [
                ['title' => 'Qué es una tesis', 'content' => "La **tesis** es la afirmación que tu texto defiende, en una o dos oraciones.\n\n- Débil: *La contaminación es un problema.*\n- Fuerte: *Ampliar el transporte público reduciría más la contaminación en Morelia que restringir la circulación de autos.*\n\nUna buena tesis se puede discutir: alguien razonable podría opinar lo contrario.",
                    'tasks' => [['title' => 'Tres tesis, de débil a fuerte', 'instructions' => "Elige un tema de tu carrera y escribe tres versiones de una tesis, de la más general a la más precisa. Explica qué cambiaste en cada paso.", 'max' => 10, 'due' => -7]]],
                ['title' => 'El párrafo argumentativo', 'content' => "Cada párrafo defiende **una** idea:\n\n1. Oración temática.\n2. Evidencia (dato, cita, ejemplo).\n3. Explicación: por qué la evidencia apoya la idea.\n4. Cierre o transición.\n\nSi un párrafo tiene dos ideas, sepáralo en dos.",
                    'tasks' => [['title' => 'Escribe un párrafo con evidencia', 'instructions' => "Escribe un párrafo de 120 a 180 palabras que apoye una de tus tesis con al menos una evidencia citada.", 'max' => 10, 'due' => 2]]],
            ]],
            ['title' => 'Fuentes y citas', 'description' => 'Citar con honestidad y elegir fuentes confiables.', 'topics' => [
                ['title' => 'Citar sin plagiar', 'content' => "Cita cuando usas palabras o ideas de otra persona.\n\n- **Cita textual:** entre comillas, con autor, año y página.\n- **Paráfrasis:** con tus palabras, pero igual con autor y año.\n\nConsulta dudas de ortografía y uso en el diccionario de la RAE.",
                    'tasks' => [['title' => 'Cita y parafrasea', 'instructions' => "Elige un párrafo de un artículo académico. Escribe una cita textual y una paráfrasis del mismo fragmento, ambas con su referencia.", 'max' => 10, 'due' => 9]]],
            ]],
        ],
        'materials' => [
            ['title' => 'Diccionario de la lengua española (RAE)', 'url' => 'https://dle.rae.es/'],
            ['title' => 'Lista de revisión de un ensayo (PDF)', 'file' => 'Lista de revisión: tesis, párrafos con una idea, evidencia citada, conclusión. Material de práctica de Gaining Grades.'],
        ],
    ],
];
