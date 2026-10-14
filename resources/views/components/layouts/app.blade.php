<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Cursos el Mai' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-canvas font-sans text-ink antialiased">
    <a href="#contenido" class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:rounded-field focus:bg-surface focus:px-4 focus:py-2">Saltar al contenido</a>

    @auth
        <header class="border-b border-line bg-surface">
            <div class="mx-auto flex max-w-[1120px] items-center justify-between gap-4 px-4 py-3">
                <a href="{{ route('dashboard') }}" class="font-serif text-xl font-semibold">Cursos el Mai</a>
                <div class="flex items-center gap-4">
                    <span class="hidden text-muted sm:inline">{{ auth()->user()->name }}</span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="min-h-11 rounded-field border border-line-strong px-4 hover:bg-sunken">Cerrar sesión</button>
                    </form>
                </div>
            </div>
        </header>
    @endauth

    <main id="contenido" class="mx-auto max-w-[1120px] px-4 py-8">
        {{ $slot }}
    </main>
</body>
</html>
