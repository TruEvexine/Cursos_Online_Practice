@php
    // Lo que cada área mostrará en próximas fases (docs/DESIGN.md, "Pantallas de inicio por rol").
    [$heading, $next] = [
        'super_admin' => ['Administración', 'Aquí verás los usuarios, la bitácora reciente y el estado del sistema.'],
        'director' => ['Monitoreo', 'Aquí verás el tablero de ponentes y alumnos: progreso, promedios y último acceso.'],
        'ponente' => ['Tus cursos', 'Aquí verás tus cursos, los pendientes por calificar y tu próxima clase en vivo.'],
        'alumno' => ['Mis cursos', 'Aquí verás tus cursos, tu próxima entrega y los avisos recientes.'],
    ][$area];
@endphp

<x-layouts.app :title="$heading.' — Cursos el Mai'">
    <p class="mb-2 text-muted">Hola, {{ auth()->user()->name }}</p>

    <h1 class="font-serif text-3xl font-semibold">
        Has iniciado sesión como
        <span class="whitespace-nowrap rounded-full bg-primary-soft px-3 py-0.5 text-primary">{{ auth()->user()->role->name }}</span>
    </h1>

    <section class="mt-8 rounded-card border border-line bg-surface p-6">
        <h2 class="font-serif text-xl font-semibold">{{ $heading }}</h2>
        <p class="mt-2 max-w-[72ch] text-muted">{{ $next }}</p>
        <p class="mt-4 text-muted">Todavía no hay nada aquí. Mientras tanto, <a href="{{ route('profile.edit') }}" class="font-semibold text-primary underline">completa tu perfil</a>.</p>
    </section>
</x-layouts.app>
