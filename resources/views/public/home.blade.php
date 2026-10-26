@php
    $money = fn (int $n) => '$'.number_format($n);
    $email = config('site.contact_email');
    $whatsapp = preg_replace('/\D/', '', (string) config('site.whatsapp'));
    $available = [
        'Cursos con bloques y temas' => 'Organiza el curso por unidades; cada tema se escribe en Markdown y se lee en una columna cómoda.',
        'Materiales' => 'PDF, Word, PowerPoint, Excel e imágenes en almacenamiento privado; también enlaces y videos externos.',
        'Invitaciones por correo' => 'Inscribes a tus alumnos con su correo; quien no tiene cuenta recibe un enlace para fijar su contraseña.',
        'Vista previa como alumno' => 'Revisa el curso tal como lo verá un alumno, incluso en borrador, sin registrar progreso.',
        'Misiones con desbloqueo' => 'Los temas se abren en orden conforme el alumno los lee y entrega sus tareas obligatorias.',
        'Tareas y calificaciones' => 'Entregas con texto o archivo, calificación con retroalimentación, devolver para corregir y promedio ponderado.',
        'Registro abierto y catálogo' => 'Cualquier persona mayor de 18 años crea su cuenta y se inscribe desde el catálogo (pago simulado, modo prueba).',
    ];
    $soon = ['Exámenes con tiempo límite', 'Comodines por puntualidad y calidad', 'Clase en vivo y chat', 'Cobro real de cursos de pago', 'Registro de menores con autorización', 'Escuelas con sus profesores y alumnos'];
    $roles = [
        'Alumno' => 'Ve sus cursos, lee los temas y descarga los materiales. Más adelante: entrega tareas, presenta exámenes y consulta sus calificaciones.',
        'Ponente' => 'Crea y edita sus cursos, sube materiales, agrega y quita alumnos. Más adelante: califica tareas y exámenes, publica avisos y da clase en vivo.',
        'Director' => 'Consulta todos los cursos en solo lectura. Más adelante: tablero de ponentes y alumnos con progreso, promedios y último acceso.',
        'Super admin' => 'Administra todo: cursos de cualquier ponente y, más adelante, usuarios, roles y la bitácora del sistema.',
    ];
    $faq = [
        '¿Puedo crear mi cuenta yo mismo?' => 'Sí, si tienes 18 años o más: crea tu cuenta de alumno e inscríbete desde el catálogo. También puede inscribirte un ponente con tu correo; te llegará un enlace para fijar tu contraseña.',
        '¿Cuánto cuesta?' => 'Los planes de esta página son de ejemplo y pueden cambiar. Aún no cobramos en línea: si te interesa un plan, escríbenos.',
        '¿Cómo se paga?' => 'Por depósito o transferencia mientras no haya pasarela de pago. Te enviamos los datos y una referencia por correo; nunca publicamos datos bancarios en la página.',
        '¿Quién puede ver mis archivos?' => 'Los materiales se guardan en almacenamiento privado y solo los descargan quienes tienen permiso en ese curso: sus alumnos, su ponente y la dirección.',
        '¿Funciona en el celular?' => 'Sí. Todas las pantallas se adaptan al teléfono y se pueden usar con el teclado o con lector de pantalla.',
        '¿Y si soy menor de edad?' => 'La política para menores está por definir: se pedirá la autorización de madre, padre o tutor, o el alta a través de tu escuela. Lee los Términos.',
        '¿Emiten factura?' => 'Por definir. Cuando haya cobros en línea explicaremos aquí cómo pedirla.',
    ];
@endphp

<x-layouts.public title="Gaining Grades — Clases en línea, sin enredos">
    {{-- Hero: el nodo del mapa de misiones, enorme y recortado, es el único motivo de la página. --}}
    <section class="relative overflow-hidden bg-surface">
        <div aria-hidden="true" class="pointer-events-none absolute -right-[45vw] -top-[25vw] size-[120vw] rounded-full bg-primary-soft sm:-right-[30vw] sm:size-[90vw] lg:-right-[12vw] lg:-top-[18vw] lg:size-[62vw]"></div>
        <svg aria-hidden="true" class="motif-ring pointer-events-none absolute hidden sm:block -right-[45vw] -top-[25vw] size-[120vw] sm:-right-[30vw] sm:size-[90vw] lg:-right-[12vw] lg:-top-[18vw] lg:size-[62vw]" viewBox="0 0 100 100">
            <circle cx="50" cy="50" r="49.6" fill="none" stroke="currentColor" stroke-width=".3" pathLength="100" />
        </svg>
        <div class="relative mx-auto grid max-w-[1120px] gap-10 px-4 py-12 lg:grid-cols-[1fr_380px] lg:items-center lg:py-24">
            <div>
                <h1 class="max-w-[14ch] font-serif text-[clamp(2.75rem,7vw,5rem)] leading-[1.02] font-semibold tracking-tight">Clases en línea, sin enredos</h1>
                <p class="mt-6 max-w-[46ch] text-xl">Gaining Grades facilita dar y recibir clases: para ponentes independientes, escuelas y personas que aprenden por su cuenta.</p>
                <p class="mt-4 max-w-[46ch] text-muted">¿Prepa, universidad o institución? <a href="#contacto" class="font-semibold text-primary underline">Hablemos de un convenio</a>.</p>
            </div>
            <div id="acceso">
                @auth
                    <div class="rounded-card border border-line bg-surface p-6">
                        <h2 class="font-serif text-2xl font-semibold">Hola, {{ auth()->user()->name }}</h2>
                        <p class="mt-2 text-muted">Ya iniciaste sesión.</p>
                        <a href="{{ route('dashboard') }}" class="mt-6 inline-flex min-h-11 w-full items-center justify-center rounded-field bg-primary px-4 font-semibold text-canvas transition-colors duration-150 hover:bg-primary-hover">Ir a mi panel</a>
                    </div>
                @else
                    <h2 class="mb-3 font-serif text-2xl font-semibold">Iniciar sesión</h2>
                    @if (session('status'))
                        <p role="status" class="mb-3 rounded-field bg-success-soft px-3 py-2 text-success-ink"><span aria-hidden="true">✓</span> {{ session('status') }}</p>
                    @endif
                    <x-login-form />
                    @unless (Route::has('register'))
                        <p class="mt-3 text-sm text-muted">¿Sin cuenta? Hoy entras cuando un ponente te inscribe con tu correo.</p>
                    @endunless
                @endauth
            </div>
        </div>
    </section>

    @if ($featured->isNotEmpty())
        <section aria-labelledby="catalogo-titulo" class="mx-auto max-w-[1120px] px-4 pt-20">
            <div class="flex flex-wrap items-end justify-between gap-4">
                <h2 id="catalogo-titulo" class="font-serif text-4xl font-semibold">Cursos abiertos</h2>
                <a href="{{ route('catalog.index') }}" class="inline-flex min-h-11 items-center font-semibold text-primary underline">Ver todo el catálogo</a>
            </div>
            <ul class="mt-8 grid gap-6 md:grid-cols-3">
                @foreach ($featured as $course)
                    <li class="group overflow-hidden rounded-card border border-line bg-surface transition-colors duration-150 hover:border-line-strong">
                        <x-course-cover :course="$course" class="aspect-[16/9]" />
                        <div class="p-5">
                            <x-course-price :course="$course" class="text-sm font-semibold text-primary" />
                            <h3 class="mt-1 font-serif text-xl font-semibold"><a href="{{ route('catalog.show', $course) }}" class="hover:underline">{{ $course->title }}</a></h3>
                            <p class="mt-1 text-muted">Imparte {{ $course->ponente->name }}</p>
                        </div>
                    </li>
                @endforeach
            </ul>
        </section>
    @endif

    <section id="para-quien" aria-labelledby="para-quien-titulo" class="mx-auto max-w-[1120px] px-4 py-20">
        <h2 id="para-quien-titulo" class="font-serif text-4xl font-semibold">Para quién es</h2>
        <div class="mt-10 grid gap-10 md:grid-cols-3">
            @foreach ([
                ['Ponentes independientes', 'Crea tus cursos y compártelos con tus alumnos. Más adelante podrás promocionarlos y cobrar inscripciones, tanto en cursos on demand que crecen poco a poco como en cursos pregrabados.'],
                ['Escuelas', 'Reúne a tus profesores y alumnos en un mismo lugar, con cursos ordenados y materiales privados. (Escuelas con su propio espacio: próximamente.)'],
                ['Personas que aprenden por su cuenta', 'Entra a los cursos a los que te inscriban, lee a tu ritmo desde el celular o la computadora y descarga los materiales.'],
            ] as [$who, $what])
                <div>
                    <span aria-hidden="true" class="block size-6 rounded-full border-[3px] border-primary"></span>
                    <h3 class="mt-4 font-serif text-2xl font-semibold">{{ $who }}</h3>
                    <p class="mt-2 text-lg">{{ $what }}</p>
                </div>
            @endforeach
        </div>
        <p class="mt-12 rounded-card bg-primary-soft p-6 text-lg">Prepas, universidades e instituciones: trabajamos por convenio. <a href="#contacto" class="font-semibold text-primary underline">Escríbenos</a> y lo platicamos.</p>
    </section>

    <section aria-labelledby="ofrecemos-titulo" class="bg-surface py-20">
        <div class="mx-auto grid max-w-[1120px] gap-10 px-4 lg:grid-cols-[2fr_3fr] lg:items-center">
            <div>
                <h2 id="ofrecemos-titulo" class="font-serif text-4xl font-semibold">Qué ofrecemos</h2>
                <p class="mt-4 text-lg">Un lugar sencillo para armar un curso y que tus alumnos lo encuentren claro, con permisos cuidados y sin distracciones.</p>
                <ul class="mt-6 space-y-3 text-lg">
                    @foreach (['El curso ordenado por bloques y temas, con los resultados esperados de cada bloque.', 'Materiales y enlaces junto al tema que los usa.', 'Cada quien ve solo lo que le toca: los alumnos inscritos, su ponente y la dirección.', 'Pantallas legibles, con tema oscuro y pensadas para el teléfono.'] as $item)
                        <li class="flex gap-3"><span aria-hidden="true" class="mt-2 size-3 shrink-0 rounded-full bg-primary"></span>{{ $item }}</li>
                    @endforeach
                </ul>
            </div>
            <figure>
                <img src="{{ asset('img/portada/temario.webp') }}" width="1280" height="800" loading="lazy" class="w-full rounded-card border border-line"
                     alt="Captura del temario de un curso en Gaining Grades: bloques con sus temas y los materiales del curso.">
                <figcaption class="mt-2 text-sm text-muted">Captura real de la app con datos de prueba.</figcaption>
            </figure>
        </div>
    </section>

    <section aria-labelledby="como-titulo" class="mx-auto max-w-[1120px] px-4 py-20">
        <h2 id="como-titulo" class="font-serif text-4xl font-semibold">Cómo funciona</h2>
        {{-- Los pasos son nodos en un camino, como el mapa de misiones. --}}
        <ol class="mt-10 space-y-16">
            @foreach ([
                ['Armas tu curso', 'Crea bloques y temas, escribe el contenido y sube tus materiales. Revisa todo con la vista previa antes de publicarlo.', 'constructor', 'Captura del constructor de un curso: bloques con sus temas y botones para agregar y editar.'],
                ['Invitas a tus alumnos', 'Agrégalos con su correo. Si no tienen cuenta, les llega un enlace para fijar su contraseña.', 'alumnos', 'Captura de la lista de alumnos de un curso con su estado y el formulario para agregar uno.'],
                ['Ellos aprenden a su ritmo', 'Cada alumno ve sus cursos, recorre el temario y lee cada tema con sus materiales.', 'tema', 'Captura de un tema abierto por un alumno, con el texto del tema y sus materiales.'],
            ] as [$step, $text, $shot, $alt])
                <li class="grid gap-6 lg:grid-cols-[2fr_3fr] lg:items-center">
                    <div class="flex gap-4">
                        <span aria-hidden="true" class="mt-1 size-8 shrink-0 rounded-full border-4 border-primary {{ $loop->last ? 'bg-primary' : 'bg-surface' }}"></span>
                        <div>
                            <h3 class="font-serif text-2xl font-semibold">{{ $step }}</h3>
                            <p class="mt-2 text-lg">{{ $text }}</p>
                        </div>
                    </div>
                    <img src="{{ asset("img/portada/$shot.webp") }}" width="1280" height="800" loading="lazy" alt="{{ $alt }}" class="w-full rounded-card border border-line">
                </li>
            @endforeach
        </ol>
    </section>

    <section id="roles" aria-labelledby="roles-titulo" class="bg-surface py-20">
        <div class="mx-auto max-w-[1120px] px-4">
            <h2 id="roles-titulo" class="font-serif text-4xl font-semibold">Roles</h2>
            <p class="mt-4 max-w-[60ch] text-lg">Cada persona entra con un rol y solo puede hacer lo que ese rol permite; el servidor lo revisa en cada acción.</p>
            <dl class="mt-10 grid gap-x-12 gap-y-8 md:grid-cols-2">
                @foreach ($roles as $role => $does)
                    <div>
                        <dt class="font-serif text-2xl font-semibold">{{ $role }}</dt>
                        <dd class="mt-2 text-lg">{{ $does }}</dd>
                    </div>
                @endforeach
            </dl>
        </div>
    </section>

    <section id="funcionalidades" aria-labelledby="func-titulo" class="mx-auto max-w-[1120px] px-4 py-20">
        <h2 id="func-titulo" class="font-serif text-4xl font-semibold">Funcionalidades</h2>
        <div class="mt-10 grid gap-12 lg:grid-cols-[3fr_2fr]">
            <div>
                <h3 class="font-serif text-2xl font-semibold">Disponible hoy</h3>
                <ul class="mt-4 space-y-6">
                    @foreach ($available as $name => $detail)
                        <li class="flex gap-3">
                            <svg aria-hidden="true" viewBox="0 0 24 24" class="mt-1 size-6 shrink-0 rounded-full bg-success-soft p-1 text-success-ink"><path d="M5 12.5l4.5 4.5L19 7.5" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            <div><p class="text-lg font-semibold">{{ $name }} <span class="sr-only">(disponible)</span></p><p class="text-lg">{{ $detail }}</p></div>
                        </li>
                    @endforeach
                </ul>
            </div>
            <div class="rounded-card bg-sunken p-6">
                <h3 class="font-serif text-2xl font-semibold">Próximamente</h3>
                <ul class="mt-4 space-y-3">
                    @foreach ($soon as $name)
                        <li class="flex gap-3 text-lg"><span aria-hidden="true" class="mt-2 size-3 shrink-0 rounded-full border-2 border-line-strong"></span>{{ $name }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </section>

    <section id="planes" aria-labelledby="planes-titulo" class="bg-surface py-20">
        <div class="mx-auto max-w-[1120px] px-4">
            <h2 id="planes-titulo" class="font-serif text-4xl font-semibold">Planes</h2>
            <p class="mt-4 max-w-[60ch] text-lg"><strong>Ejemplo, sujetas a cambio.</strong> Precios mensuales en pesos mexicanos; todavía no cobramos en línea.</p>
            <ul class="mt-10 space-y-3">
                @foreach (config('site.plans') as $plan)
                    <li class="grid gap-2 rounded-card bg-canvas p-6 md:grid-cols-[1fr_1fr_2fr] md:gap-8">
                        <h3 class="font-serif text-2xl font-semibold">{{ $plan['name'] }}</h3>
                        <p class="text-2xl font-semibold tabular-nums">
                            @if ($plan['price'] === null)
                                Por contacto
                            @else
                                {{ $money($plan['price']) }}<span class="text-base font-normal text-muted"> {{ $plan['price'] ? 'al mes' : '' }} · ejemplo</span>
                            @endif
                        </p>
                        <div class="text-lg">
                            <p class="font-semibold">{{ $plan['for'] }}</p>
                            <p>{{ $plan['includes'] }}</p>
                        </div>
                    </li>
                @endforeach
            </ul>

            <div id="simulador" class="mt-12 grid gap-8 rounded-card bg-primary-soft p-6 md:p-10 lg:grid-cols-2">
                <div>
                    <h3 class="font-serif text-2xl font-semibold">Calcula el plan Escuela</h3>
                    <p class="mt-2 text-lg">Base de {{ $money(config('site.school.base')) }} con {{ config('site.school.included_teachers') }} ponentes y {{ config('site.school.included_students') }} alumnos; {{ $money(config('site.school.per_extra_student')) }} por alumno extra y {{ $money(config('site.school.per_extra_teacher')) }} por ponente extra. Ejemplo, sujeto a cambio.</p>
                </div>
                <form method="GET" action="{{ route('home') }}#simulador" class="space-y-4" novalidate>
                    <div class="grid gap-4 sm:grid-cols-2">
                        @foreach (['alumnos' => 'Alumnos', 'ponentes' => 'Ponentes'] as $field => $label)
                            @php $error = $simulatorErrors?->first($field); @endphp
                            <div>
                                <label for="sim-{{ $field }}" class="mb-1 block font-semibold">{{ $label }}</label>
                                <input id="sim-{{ $field }}" name="{{ $field }}" type="number" min="0" max="5000" step="1" inputmode="numeric" required
                                       value="{{ request($field) }}" @if ($error) aria-invalid="true" aria-describedby="sim-{{ $field }}-error" @endif
                                       class="min-h-11 w-full rounded-field border border-line-strong bg-surface px-3">
                                @if ($error)<p id="sim-{{ $field }}-error" class="mt-1 text-danger"><span aria-hidden="true">⚠</span> {{ $error }}</p>@endif
                            </div>
                        @endforeach
                    </div>
                    <button type="submit" class="min-h-11 rounded-field bg-primary px-6 font-semibold text-canvas transition-colors duration-150 hover:bg-primary-hover">Calcular</button>
                    @if ($quote)
                        <p role="status" class="text-xl">
                            @if ($quote['amount'] === null)
                                Con más de {{ number_format(config('site.school.custom_above_students')) }} alumnos o {{ config('site.school.custom_above_teachers') }} ponentes te conviene un <strong>plan a medida</strong>: <a href="#contacto" class="font-semibold text-primary underline">contáctanos</a>.
                            @else
                                Total de ejemplo: <strong class="tabular-nums">{{ $money($quote['amount']) }} al mes</strong>.
                            @endif
                        </p>
                    @endif
                </form>
            </div>
        </div>
    </section>

    <section aria-labelledby="pago-titulo" class="mx-auto max-w-[1120px] px-4 py-20">
        <h2 id="pago-titulo" class="font-serif text-4xl font-semibold">Modalidades de pago</h2>
        <p class="mt-4 max-w-[60ch] text-lg">Mientras no haya pasarela de pago, se paga por <strong>depósito o transferencia</strong>. No pedimos datos de tarjeta y no publicamos datos bancarios en esta página.</p>
        <ol class="mt-10 grid gap-8 md:grid-cols-4">
            @foreach (['Lo solicitas' => 'Nos escribes con el plan o el curso que te interesa.', 'Recibes los datos' => 'Te enviamos por correo los datos de pago y una referencia.', 'Envías tu comprobante' => 'Respondes con el comprobante del depósito o la transferencia.', 'Activamos' => 'Confirmamos el pago y activamos tu plan o tu inscripción.'] as $step => $text)
                <li class="flex gap-3 md:block">
                    <span aria-hidden="true" class="mt-1 block size-6 shrink-0 rounded-full border-[3px] border-primary {{ $loop->last ? 'bg-primary' : '' }}"></span>
                    <div class="md:mt-4"><h3 class="font-serif text-xl font-semibold">{{ $step }}</h3><p class="mt-1 text-lg">{{ $text }}</p></div>
                </li>
            @endforeach
        </ol>
        <p class="mt-10 text-lg"><strong>Pasarela de pago y facturación:</strong> por definir.</p>
    </section>

    <section id="preguntas" aria-labelledby="faq-titulo" class="bg-surface py-20">
        <div class="mx-auto grid max-w-[1120px] gap-10 px-4 lg:grid-cols-[1fr_2fr]">
            <h2 id="faq-titulo" class="font-serif text-4xl font-semibold">Preguntas frecuentes</h2>
            <div class="space-y-3">
                @foreach ($faq as $question => $answer)
                    <details class="group rounded-card bg-canvas px-5 py-2" @if ($loop->first) open @endif>
                        <summary class="flex min-h-11 cursor-pointer list-none items-center justify-between gap-4 text-lg font-semibold [&::-webkit-details-marker]:hidden">
                            {{ $question }}
                            <span aria-hidden="true" class="size-4 shrink-0 rounded-full border-2 border-primary transition-colors duration-150 group-open:bg-primary"></span>
                        </summary>
                        <p class="max-w-[60ch] pb-4 text-lg">{{ $answer }}</p>
                    </details>
                @endforeach
            </div>
        </div>
    </section>

    <section id="contacto" aria-labelledby="contacto-titulo" class="mx-auto max-w-[1120px] px-4 py-20">
        <h2 id="contacto-titulo" class="font-serif text-4xl font-semibold">Contacto</h2>
        <p class="mt-4 max-w-[60ch] text-lg">Para planes, convenios con instituciones o dudas, escríbenos. Te respondemos por el mismo medio.</p>
        <ul class="mt-8 flex flex-col gap-4 sm:flex-row sm:gap-10">
            @if ($email)
                <li><a href="mailto:{{ $email }}?subject={{ rawurlencode('Información sobre Gaining Grades') }}" class="inline-flex min-h-11 items-center break-all text-2xl font-semibold text-primary underline">{{ $email }}</a></li>
            @endif
            @if ($whatsapp)
                <li><a href="https://wa.me/{{ $whatsapp }}" target="_blank" rel="noopener noreferrer" class="inline-flex min-h-11 items-center text-2xl font-semibold text-primary underline">WhatsApp {{ preg_replace('/^(\d{3})(\d{3})(\d{4})$/', '$1 $2 $3', substr($whatsapp, -10)) }}<span class="sr-only"> (se abre en otra pestaña)</span></a></li>
            @endif
        </ul>
    </section>
</x-layouts.public>
