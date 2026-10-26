<x-layouts.app :title="'Entregas: '.$course->title.' — Gaining Grades'"
               :breadcrumbs="['Inicio' => route('dashboard'), 'Cursos' => route('courses.index'), $course->title => route('blocks.index', $course), 'Entregas' => null]">
    <h1 class="mb-4 font-serif text-3xl font-semibold">{{ $course->title }}</h1>
    <x-course-tabs :course="$course" current="grading" />

    <h2 class="mb-4 font-serif text-2xl font-semibold">Por calificar</h2>
    @if ($pending->isEmpty())
        <section class="max-w-2xl rounded-card border border-line bg-surface p-6">
            <p class="font-semibold">No hay entregas pendientes.</p>
            <p class="mt-1 text-muted">Cuando un alumno entregue una tarea, aparecerá aquí, la más antigua primero.</p>
            <a href="{{ route('blocks.index', $course) }}" class="mt-2 inline-flex min-h-11 items-center font-semibold text-primary underline">Ir al contenido del curso</a>
        </section>
    @else
        {{-- Menos de 768 px: cada fila se vuelve tarjeta con etiquetas (docs/DESIGN.md §4). --}}
        <table class="w-full md:rounded-card md:border md:border-line md:bg-surface">
            <thead class="sr-only md:not-sr-only">
                <tr class="border-b border-line text-left text-sm text-muted">
                    <th scope="col" class="px-4 py-3 font-semibold">Alumno</th>
                    <th scope="col" class="px-4 py-3 font-semibold">Tarea</th>
                    <th scope="col" class="px-4 py-3 font-semibold">Entregada</th>
                    <th scope="col" class="px-4 py-3"><span class="sr-only">Acciones</span></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($pending as $submission)
                    <tr class="mb-3 block rounded-card border border-line bg-surface p-4 md:mb-0 md:table-row md:rounded-none md:border-0 md:border-b md:p-0 md:last:border-b-0">
                        <td class="block pb-2 font-semibold md:table-cell md:px-4 md:py-3 [overflow-wrap:anywhere]">{{ $submission->user->name }}</td>
                        <td class="flex justify-between gap-4 py-1 md:table-cell md:px-4 md:py-3"><span class="text-muted md:hidden">Tarea</span>
                            <span class="text-right md:text-left [overflow-wrap:anywhere]">{{ $submission->task->title }}<span class="block text-sm text-muted">{{ $submission->task->topic->title }}</span></span></td>
                        <td class="flex justify-between gap-4 py-1 md:table-cell md:px-4 md:py-3 md:whitespace-nowrap"><span class="text-muted md:hidden">Entregada</span>
                            <span>{{ $submission->updated_at->timezone('America/Mexico_City')->isoFormat('D MMM, H:mm') }}
                                @if ($submission->isLate())<span class="ml-1 rounded-full bg-warning-soft px-2 text-sm font-semibold text-warning-ink">Tarde</span>@endif</span></td>
                        <td class="pt-2 md:table-cell md:px-4 md:py-3 md:text-right">
                            <a href="{{ route('grading.show', $submission) }}" class="inline-flex min-h-11 items-center font-semibold text-primary underline">Calificar<span class="sr-only"> la entrega de {{ $submission->user->name }}</span></a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</x-layouts.app>
