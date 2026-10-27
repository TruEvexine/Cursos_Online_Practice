@props(['title' => 'Gaining Grades', 'breadcrumbs' => []])

@php
    $user = auth()->user();
    $nav = [];
    if ($user?->hasVerifiedEmail()) {
        $nav[] = $user->hasRole('alumno') ? ['Mis cursos', 'alumno.home', ['alumno.*', 'reader.*']] : ['Inicio', $user->role->slug.'.home'];
        if ($user->hasRole('super_admin')) {
            $nav[] = ['Monitoreo', 'director.home'];
        }
        if ($user->can('viewAny', App\Models\Course::class)) {
            $nav[] = [$user->hasRole('ponente') ? 'Mis cursos' : 'Cursos', 'courses.index', ['courses.*', 'blocks.*', 'topics.*', 'tasks.*', 'materials.*', 'students.*', 'grading.*', 'reader.*']];
        }
        $nav[] = ['Mi perfil', 'profile.edit'];
    }
@endphp

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }}</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-canvas font-sans text-ink antialiased">
    {{ $banner ?? '' }}
    <a href="#contenido" class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:rounded-field focus:bg-surface focus:px-4 focus:py-2">Saltar al contenido</a>

    @auth
        <header class="border-b border-line bg-surface">
            <div class="flex items-center justify-between gap-4 px-4 py-3">
                <a href="{{ route('dashboard') }}" class="inline-flex min-h-11 items-center whitespace-nowrap font-serif text-xl font-semibold">Gaining Grades</a>
                <div class="flex items-center gap-3">
                    <span class="hidden text-muted sm:inline">{{ $user->name }}</span>
                    {{-- En móvil no cabe junto al botón; el rol también se ve en la página de inicio. --}}
                    <span class="hidden whitespace-nowrap rounded-full bg-primary-soft px-3 py-0.5 text-sm font-semibold text-primary sm:inline-block">{{ $user->role->name }}</span>
                    <button type="button" data-theme-toggle class="min-h-11 whitespace-nowrap rounded-field border border-line-strong px-4 hover:bg-sunken">Modo oscuro</button>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="min-h-11 whitespace-nowrap rounded-field border border-line-strong px-4 hover:bg-sunken">Cerrar sesión</button>
                    </form>
                </div>
            </div>
        </header>
    @endauth

    <div class="lg:flex">
        @if ($nav)
            <nav aria-label="Principal" class="border-b border-line bg-surface lg:min-h-[calc(100vh-4.5rem)] lg:w-56 lg:shrink-0 lg:border-b-0 lg:border-r">
                <ul class="flex gap-1 overflow-x-auto px-2 py-2 lg:flex-col">
                    @foreach ($nav as $item)
                        @php
                            [$label, $route] = $item;
                            $active = request()->routeIs(...($item[2] ?? [Str::beforeLast($route, '.').'.*']));
                        @endphp
                        <li>
                            <a href="{{ route($route) }}" @if ($active) aria-current="page" @endif
                               class="flex min-h-11 items-center whitespace-nowrap rounded-field px-3 {{ $active ? 'bg-primary-soft font-semibold text-primary' : 'hover:bg-sunken' }}">
                                {{ $label }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </nav>
        @endif

        <main id="contenido" class="mx-auto w-full max-w-[1120px] px-4 py-8">
            @if ($breadcrumbs)
                <nav aria-label="Migas de pan" class="mb-4 text-sm text-muted">
                    <ol class="flex flex-wrap items-center gap-x-2">
                        @foreach ($breadcrumbs as $label => $url)
                            <li class="whitespace-nowrap">@if ($url)<a href="{{ $url }}" class="inline-flex min-h-11 items-center underline">{{ Str::limit($label, 32) }}</a> <span aria-hidden="true">/</span>@else<span aria-current="page">{{ Str::limit($label, 32) }}</span>@endif</li>
                        @endforeach
                    </ol>
                </nav>
            @endif

            @if (session('status') && session('status') !== 'verification-link-sent')
                <div role="status" class="mb-6 rounded-field border border-line bg-surface px-4 py-3">
                    <span aria-hidden="true">✓</span> {{ session('status') }}
                </div>
            @endif

            {{ $slot }}
        </main>
    </div>
</body>
</html>
