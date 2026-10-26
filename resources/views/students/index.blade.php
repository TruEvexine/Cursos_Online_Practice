@php $statuses = ['active' => ['Inscrito', 'bg-primary-soft text-primary'], 'completed' => ['Terminó', 'bg-success-soft text-success-ink'], 'removed' => ['Quitado', 'bg-sunken text-muted']]; @endphp

<x-layouts.app :title="'Alumnos: '.$course->title.' — Gaining Grades'"
               :breadcrumbs="['Inicio' => route('dashboard'), 'Cursos' => route('courses.index'), $course->title => route('blocks.index', $course), 'Alumnos' => null]">
    <h1 class="mb-4 font-serif text-4xl font-semibold">{{ $course->title }}</h1>
    <x-course-tabs :course="$course" current="students" />

    @if ($enrollments->isEmpty())
        <section class="mb-6 rounded-card border border-line bg-surface p-6">
            <h2 class="font-serif text-xl font-semibold">Este curso todavía no tiene alumnos</h2>
            <p class="mt-2 max-w-[72ch] text-muted">Agrégalos con su correo. Si todavía no tienen cuenta, se crea una y les llega un correo para fijar su contraseña.</p>
            <a href="#agregar-alumno" class="mt-2 inline-flex min-h-11 items-center font-semibold text-primary underline">Agregar el primero</a>
        </section>
    @else
        {{-- Menos de 768 px: cada fila se vuelve tarjeta con etiquetas (docs/DESIGN.md §4). --}}
        <table class="mb-8 w-full md:rounded-card md:border md:border-line md:bg-surface">
            <thead class="sr-only md:not-sr-only">
                <tr class="border-b border-line text-left text-sm text-muted">
                    <th scope="col" class="px-4 py-3 font-semibold">Alumno</th>
                    <th scope="col" class="px-4 py-3 font-semibold">Estado</th>
                    <th scope="col" class="px-4 py-3 font-semibold">Inscrito el</th>
                    <th scope="col" class="px-4 py-3"><span class="sr-only">Acciones</span></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($enrollments as $enrollment)
                    @php [$label, $classes] = $statuses[$enrollment->status]; @endphp
                    <tr class="mb-3 block rounded-card border border-line bg-surface p-4 md:mb-0 md:table-row md:rounded-none md:border-0 md:border-b md:p-0 md:last:border-b-0">
                        <td class="block pb-2 md:table-cell md:px-4 md:py-3">
                            <span class="font-semibold [overflow-wrap:anywhere]">{{ $enrollment->user->name }}</span>
                            <p class="text-sm text-muted [overflow-wrap:anywhere]">{{ $enrollment->user->email }}</p>
                        </td>
                        <td class="flex justify-between gap-4 py-1 md:table-cell md:px-4 md:py-3"><span class="text-muted md:hidden">Estado</span><span class="inline-block whitespace-nowrap rounded-full px-3 py-0.5 text-sm font-semibold {{ $classes }}">{{ $label }}</span></td>
                        <td class="flex justify-between gap-4 py-1 md:table-cell md:whitespace-nowrap md:px-4 md:py-3"><span class="text-muted md:hidden">Inscrito el</span>{{ $enrollment->enrolled_at->timezone('America/Mexico_City')->isoFormat('D MMM YYYY') }}</td>
                        <td class="pt-2 md:table-cell md:px-4 md:py-3 md:text-right">
                            @if ($enrollment->status !== 'removed')
                                <details>
                                    <summary class="inline-flex min-h-11 cursor-pointer items-center font-semibold text-danger">Quitar<span class="sr-only"> a {{ $enrollment->user->name }}</span></summary>
                                    <p class="py-2 text-sm md:max-w-64 md:text-left">Dejará de ver el curso. Su historial se conserva y puedes volver a agregar su correo.</p>
                                    <form method="POST" action="{{ route('students.destroy', $enrollment) }}" class="pb-2">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="min-h-11 whitespace-nowrap rounded-field border border-danger px-4 font-semibold text-danger hover:bg-danger-soft">Sí, quitar del curso</button>
                                    </form>
                                </details>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <form id="agregar-alumno" method="POST" action="{{ route('students.store', $course) }}" class="max-w-2xl space-y-6 rounded-card border border-line bg-surface p-6" novalidate>
        @csrf
        <h2 class="font-serif text-xl font-semibold">Agregar alumno</h2>
        <x-field name="email" label="Correo" type="email" required maxlength="190" autocomplete="off" />
        <x-field name="name" label="Nombre (solo si todavía no tiene cuenta)" maxlength="120" autocomplete="off"
                 help="Si el correo es nuevo, creamos su cuenta de alumno y le enviamos un enlace para fijar su contraseña." />
        <button type="submit" class="min-h-11 rounded-field bg-primary px-6 font-semibold text-canvas hover:bg-primary-hover">Agregar al curso</button>
    </form>
</x-layouts.app>
