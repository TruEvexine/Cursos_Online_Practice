@php $total = $pending->sum(); @endphp

<x-layouts.app title="Inicio — Gaining Grades">
    <p class="mb-2 text-muted">Hola, {{ auth()->user()->name }}</p>
    <div class="mb-8 flex flex-wrap items-end justify-between gap-4">
        <h1 class="font-serif text-4xl font-semibold">Tus cursos</h1>
        <a href="{{ route('courses.create') }}" class="inline-flex min-h-11 items-center rounded-field bg-primary px-6 font-semibold text-canvas transition-colors duration-150 hover:bg-primary-hover">Nuevo curso</a>
    </div>

    <section aria-labelledby="pendientes" class="mb-8 rounded-card {{ $total ? 'bg-primary-soft' : 'border border-line bg-surface' }} p-6">
        <h2 id="pendientes" class="font-semibold">Por calificar</h2>
        @if ($total)
            <p class="mt-1 font-serif text-4xl font-semibold tabular-nums">{{ $total }}<span class="text-lg font-normal font-sans"> {{ $total === 1 ? 'entrega espera' : 'entregas esperan' }} tu revisión</span></p>
            <p class="mt-1 text-sm text-muted">Entra a un curso con pendientes para calificar, la más antigua primero.</p>
        @else
            <p class="mt-1 text-lg">Estás al día: no hay entregas pendientes.</p>
        @endif
    </section>

    @if ($courses->isEmpty())
        <section class="max-w-2xl rounded-card border border-line bg-surface p-6">
            <h2 class="font-serif text-xl font-semibold">Todavía no tienes cursos</h2>
            <p class="mt-2 text-muted">Crea el primero; puedes dejarlo en borrador hasta que esté listo.</p>
            <a href="{{ route('courses.create') }}" class="mt-2 inline-flex min-h-11 items-center font-semibold text-primary underline">Crear un curso</a>
        </section>
    @else
        <ul class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($courses as $course)
                <li class="flex flex-col overflow-hidden rounded-card border border-line bg-surface transition-colors duration-150 hover:border-line-strong">
                    <x-course-cover :course="$course" class="aspect-[16/7]" />
                    <div class="flex flex-1 flex-col p-5">
                        <x-course-status :status="$course->status" class="self-start" />
                        <h2 class="mt-2 font-serif text-xl font-semibold [overflow-wrap:anywhere]">{{ $course->title }}</h2>
                        <p class="mt-1 text-sm text-muted">{{ $course->students_count === 1 ? '1 alumno activo' : $course->students_count.' alumnos activos' }}</p>
                        @if ($n = $pending[$course->id] ?? 0)
                            <a href="{{ route('grading.index', $course) }}" class="mt-3 inline-flex min-h-11 items-center self-start rounded-full bg-primary px-4 text-sm font-semibold text-canvas hover:bg-primary-hover">{{ $n }} por calificar<span class="sr-only"> en «{{ $course->title }}»</span></a>
                        @endif
                        <div class="mt-auto flex flex-wrap gap-x-4 pt-4">
                            <a href="{{ route('blocks.index', $course) }}" class="inline-flex min-h-11 items-center font-semibold text-primary underline">Contenido<span class="sr-only"> de «{{ $course->title }}»</span></a>
                            <a href="{{ route('reader.course', $course) }}" class="inline-flex min-h-11 items-center font-semibold text-primary underline">Vista previa<span class="sr-only"> de «{{ $course->title }}»</span></a>
                        </div>
                    </div>
                </li>
            @endforeach
        </ul>
    @endif
</x-layouts.app>
