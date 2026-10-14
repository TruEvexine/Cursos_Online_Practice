@php
    // Lo que cada rol verá aquí en próximas fases (docs/DESIGN.md, "Pantallas de inicio por rol").
    $next = [
        'super_admin' => 'Aquí verás los usuarios, la bitácora reciente y el estado del sistema.',
        'director' => 'Aquí verás el tablero de ponentes y alumnos: progreso, promedios y último acceso.',
        'ponente' => 'Aquí verás tus cursos, los pendientes por calificar y tu próxima clase en vivo.',
        'alumno' => 'Aquí verás tus cursos, tu próxima entrega y los avisos recientes.',
    ][$role->slug] ?? '';
@endphp

<x-layouts.app title="Inicio — Cursos el Mai">
    <p class="mb-2 text-muted">Hola, {{ auth()->user()->name }}</p>

    <h1 class="font-serif text-3xl font-semibold">
        Has iniciado sesión como
        <span class="whitespace-nowrap rounded-full bg-primary-soft px-3 py-0.5 text-primary">{{ $role->name }}</span>
    </h1>

    <section class="mt-8 rounded-card border border-line bg-surface p-6">
        <h2 class="font-serif text-xl font-semibold">Tu inicio</h2>
        <p class="mt-2 max-w-[72ch] text-muted">{{ $next }}</p>
    </section>
</x-layouts.app>
