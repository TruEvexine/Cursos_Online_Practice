<x-layouts.public title="Catálogo de cursos — Gaining Grades" description="Cursos abiertos de Gaining Grades: inscríbete con tu cuenta de alumno.">
    <section class="mx-auto max-w-[1120px] px-4 py-16">
        <h1 class="font-serif text-5xl font-semibold">Catálogo de cursos</h1>
        <p class="mt-4 max-w-[60ch] text-lg">Cursos abiertos a cualquier persona con cuenta de alumno. En esta versión de prueba, los cursos de pago usan un <strong>pago simulado</strong>: no se cobra nada.</p>

        @if ($courses->isEmpty())
            <div class="mt-10 max-w-2xl rounded-card bg-surface p-6">
                <p class="text-lg font-semibold">Todavía no hay cursos en el catálogo.</p>
                <a href="{{ route('home') }}#contacto" class="mt-2 inline-flex min-h-11 items-center font-semibold text-primary underline">Escríbenos si quieres publicar uno</a>
            </div>
        @else
            <ul class="mt-10 grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                @foreach ($courses as $course)
                    <li class="flex flex-col overflow-hidden rounded-card border border-line bg-surface transition-colors duration-150 hover:border-line-strong">
                        <x-course-cover :course="$course" class="aspect-[16/7]" />
                        <div class="flex flex-1 flex-col p-6">
                        <x-course-price :course="$course" class="self-start rounded-full bg-primary-soft px-3 py-0.5 text-sm font-semibold text-primary" />
                        <h2 class="mt-3 font-serif text-2xl font-semibold [overflow-wrap:anywhere]">{{ $course->title }}</h2>
                        <p class="mt-1 text-muted">Imparte {{ $course->ponente->name }}</p>
                        @if ($course->summary)<p class="mt-3 text-lg">{{ $course->summary }}</p>@endif
                        <p class="mt-3 text-sm text-muted">
                            {{ $course->blocks_count === 1 ? '1 misión' : $course->blocks_count.' misiones' }}
                            @if ($course->starts_on) · inicia el {{ $course->starts_on->isoFormat('D MMM YYYY') }}@endif
                            · actualizado el {{ $course->updated_at->timezone('America/Mexico_City')->isoFormat('D MMM YYYY') }}
                        </p>
                        <a href="{{ route('catalog.show', $course) }}" class="mt-auto inline-flex min-h-11 items-center self-start pt-4 font-semibold text-primary underline">Ver el curso<span class="sr-only"> «{{ $course->title }}»</span></a>
                        </div>
                    </li>
                @endforeach
            </ul>
            <div class="mt-8">{{ $courses->links() }}</div>
        @endif
    </section>
</x-layouts.public>
