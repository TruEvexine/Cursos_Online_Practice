<x-layouts.app title="Gaining Grades">
    <section class="py-16 text-center">
        <h1 class="font-serif text-4xl font-semibold">Gaining Grades</h1>
        <p class="mx-auto mt-4 max-w-[60ch] text-lg text-muted">Plataforma de cursos en línea para alumnos y ponentes.</p>
        <a href="{{ auth()->check() ? route('dashboard') : route('login') }}"
           class="mt-8 inline-flex min-h-11 items-center rounded-field bg-primary px-6 font-semibold text-canvas hover:bg-primary-hover">
            {{ auth()->check() ? 'Ir a mi inicio' : 'Iniciar sesión' }}
        </a>
    </section>
</x-layouts.app>
