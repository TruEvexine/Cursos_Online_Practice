@php $showPonente = ! auth()->user()->hasRole('ponente'); @endphp

<x-layouts.app title="Cursos — Gaining Grades" :breadcrumbs="['Inicio' => route('dashboard'), 'Cursos' => null]">
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <h1 class="font-serif text-3xl font-semibold">{{ $showPonente ? 'Cursos' : 'Mis cursos' }}</h1>
        @can('create', App\Models\Course::class)
            <a href="{{ route('courses.create') }}" class="inline-flex min-h-11 items-center rounded-field bg-primary px-6 font-semibold text-canvas hover:bg-primary-hover">Nuevo curso</a>
        @endcan
    </div>

    @if ($courses->isEmpty())
        <section class="rounded-card border border-line bg-surface p-6">
            <h2 class="font-serif text-xl font-semibold">Todavía no hay cursos</h2>
            @can('create', App\Models\Course::class)
                <p class="mt-2 text-muted">Crea tu primer curso; puedes dejarlo en borrador hasta que esté listo.</p>
                <a href="{{ route('courses.create') }}" class="mt-4 inline-flex min-h-11 items-center font-semibold text-primary underline">Crear un curso</a>
            @else
                <p class="mt-2 text-muted">Cuando los ponentes creen cursos, aparecerán aquí. <a href="{{ route('dashboard') }}" class="font-semibold text-primary underline">Volver al inicio</a></p>
            @endcan
        </section>
    @else
        {{-- Menos de 768 px: cada fila se vuelve tarjeta con etiquetas (docs/DESIGN.md §4). --}}
        <table class="w-full md:rounded-card md:border md:border-line md:bg-surface">
            <thead class="sr-only md:not-sr-only">
                <tr class="border-b border-line text-left text-sm text-muted">
                    <th scope="col" class="px-4 py-3 font-semibold">Curso</th>
                    @if ($showPonente)<th scope="col" class="px-4 py-3 font-semibold">Ponente</th>@endif
                    <th scope="col" class="px-4 py-3 font-semibold">Estado</th>
                    <th scope="col" class="px-4 py-3 text-right font-semibold">Alumnos</th>
                    <th scope="col" class="px-4 py-3 font-semibold">Actualizado</th>
                    <th scope="col" class="px-4 py-3"><span class="sr-only">Acciones</span></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($courses as $course)
                    <tr class="mb-3 block rounded-card border border-line bg-surface p-4 md:mb-0 md:table-row md:rounded-none md:border-0 md:border-b md:p-0 md:last:border-b-0">
                        <td class="block pb-2 md:table-cell md:px-4 md:py-3">
                            <span class="font-semibold">{{ $course->title }}</span>
                            @if ($course->summary)<p class="text-sm text-muted">{{ Str::limit($course->summary, 90) }}</p>@endif
                        </td>
                        @if ($showPonente)
                            <td class="flex justify-between gap-4 py-1 text-right md:table-cell md:px-4 md:py-3 md:text-left"><span class="text-muted md:hidden">Ponente</span>{{ $course->ponente?->name }}</td>
                        @endif
                        <td class="flex justify-between gap-4 py-1 md:table-cell md:px-4 md:py-3"><span class="text-muted md:hidden">Estado</span><x-course-status :status="$course->status" /></td>
                        <td class="flex justify-between gap-4 py-1 tabular-nums md:table-cell md:px-4 md:py-3 md:text-right"><span class="text-muted md:hidden">Alumnos</span>{{ $course->students_count }}</td>
                        <td class="flex justify-between gap-4 py-1 md:table-cell md:px-4 md:py-3 md:whitespace-nowrap"><span class="text-muted md:hidden">Actualizado</span>{{ $course->updated_at?->timezone('America/Mexico_City')->isoFormat('D MMM YYYY') }}</td>
                        <td class="pt-2 md:table-cell md:px-4 md:py-3 md:text-right">
                            @can('update', $course)
                                <div class="flex gap-4 md:justify-end">
                                    <a href="{{ route('blocks.index', $course) }}" class="inline-flex min-h-11 items-center font-semibold text-primary underline">Contenido<span class="sr-only"> de «{{ $course->title }}»</span></a>
                                    <a href="{{ route('courses.edit', $course) }}" class="inline-flex min-h-11 items-center font-semibold text-primary underline">Datos<span class="sr-only"> de «{{ $course->title }}»</span></a>
                                </div>
                            @endcan
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="mt-6">{{ $courses->links() }}</div>
    @endif
</x-layouts.app>
