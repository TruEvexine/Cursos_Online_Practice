@props(['title' => 'Gaining Grades', 'description' => 'Gaining Grades facilita dar y recibir clases en línea: ponentes independientes, escuelas y personas que aprenden por su cuenta.'])

@php
    $home = route('home');
    $email = config('site.contact_email');
    $whatsapp = preg_replace('/\D/', '', (string) config('site.whatsapp'));
    $whatsappShown = $whatsapp ? preg_replace('/^(\d{3})(\d{3})(\d{4})$/', '$1 $2 $3', substr($whatsapp, -10)) : null;
    $footer = [
        'Producto' => ['Funcionalidades' => $home.'#funcionalidades', 'Roles' => $home.'#roles', 'Planes' => $home.'#planes'],
        'Empresa' => ['Sobre nosotros' => route('about'), 'Contacto' => $home.'#contacto'],
        'Legal' => ['Aviso de privacidad' => route('privacy'), 'Términos' => route('terms')],
    ];
@endphp

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }}</title>
    <meta name="description" content="{{ $description }}">
    <link rel="canonical" href="{{ url()->current() }}">
    <meta property="og:type" content="website">
    <meta property="og:locale" content="es_MX">
    <meta property="og:site_name" content="Gaining Grades">
    <meta property="og:title" content="{{ $title }}">
    <meta property="og:description" content="{{ $description }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:image" content="{{ asset('img/portada/temario.webp') }}">
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-svh bg-canvas font-sans text-ink antialiased">
    <a href="#contenido" class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-30 focus:rounded-field focus:bg-surface focus:px-4 focus:py-2">Saltar al contenido</a>

    <header class="bg-surface">
        <div class="mx-auto flex max-w-[1120px] items-center justify-between gap-6 px-4 py-3">
            <a href="{{ $home }}" class="inline-flex min-h-11 items-center whitespace-nowrap font-serif text-xl font-semibold">Gaining Grades</a>
            <nav aria-label="Secciones" class="hidden lg:block">
                <ul class="flex gap-6">
                    @foreach (['Para quién' => 'para-quien', 'Funcionalidades' => 'funcionalidades', 'Planes' => 'planes', 'Preguntas' => 'preguntas', 'Contacto' => 'contacto'] as $label => $anchor)
                        <li><a href="{{ $home }}#{{ $anchor }}" class="inline-flex min-h-11 items-center hover:text-primary hover:underline">{{ $label }}</a></li>
                    @endforeach
                </ul>
            </nav>
            @auth
                <a href="{{ route('dashboard') }}" class="inline-flex min-h-11 items-center whitespace-nowrap rounded-field bg-primary px-5 font-semibold text-canvas transition-colors duration-150 hover:bg-primary-hover">Ir a mi panel</a>
            @else
                <a href="{{ route('login') }}" class="inline-flex min-h-11 items-center whitespace-nowrap rounded-field bg-primary px-5 font-semibold text-canvas transition-colors duration-150 hover:bg-primary-hover">Iniciar sesión</a>
            @endauth
        </div>
    </header>

    <main id="contenido">
        {{ $slot }}
    </main>

    <footer class="bg-primary text-canvas [&_a:focus-visible]:outline-canvas">
        <div class="mx-auto max-w-[1120px] px-4 pt-16 pb-8">
            <p class="font-serif text-[clamp(2.5rem,9vw,7rem)] leading-none font-semibold tracking-tight" aria-hidden="true">Gaining Grades</p>
            <div class="mt-12 grid gap-8 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($footer as $heading => $links)
                    <nav aria-label="{{ $heading }}">
                        <h2 class="font-semibold">{{ $heading }}</h2>
                        <ul class="mt-2">
                            @foreach ($links as $label => $url)
                                <li><a href="{{ $url }}" class="inline-flex min-h-11 items-center underline-offset-4 hover:underline">{{ $label }}</a></li>
                            @endforeach
                        </ul>
                    </nav>
                @endforeach
                <div>
                    <h2 class="font-semibold">Escríbenos</h2>
                    <ul class="mt-2">
                        @if ($email)
                            <li><a href="mailto:{{ $email }}" class="inline-flex min-h-11 items-center break-all underline-offset-4 hover:underline">{{ $email }}</a></li>
                        @endif
                        @if ($whatsapp)
                            <li><a href="https://wa.me/{{ $whatsapp }}" target="_blank" rel="noopener noreferrer" class="inline-flex min-h-11 items-center underline-offset-4 hover:underline">WhatsApp {{ $whatsappShown }}<span class="sr-only"> (se abre en otra pestaña)</span></a></li>
                        @endif
                    </ul>
                </div>
            </div>
            <p class="mt-12 text-sm">© {{ now()->year }} Gaining Grades. Proyecto nacido en la Universidad Vasco de Quiroga.</p>
        </div>
    </footer>
</body>
</html>
