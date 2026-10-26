@php
    $course = $topic->block->course;
    $editing = $task->exists;
    $heading = $editing ? 'Editar tarea' : 'Nueva tarea';
    $checkbox = fn (string $name, string $label, string $help) => compact('name', 'label', 'help');
@endphp

<x-layouts.app :title="$heading.' — Gaining Grades'"
               :breadcrumbs="['Inicio' => route('dashboard'), 'Cursos' => route('courses.index'), $course->title => route('blocks.index', $course), $topic->title => route('topics.edit', $topic), $heading => null]">
    <p class="text-muted">Tema: {{ $topic->title }}</p>
    <h1 class="mb-6 font-serif text-4xl font-semibold">{{ $heading }}</h1>

    <form method="POST" action="{{ $editing ? route('tasks.update', $task) : route('tasks.store', $topic) }}"
          class="max-w-3xl space-y-6 rounded-card border border-line bg-surface p-6" novalidate>
        @csrf
        @if ($editing) @method('PUT') @endif

        <x-field name="title" label="Título de la tarea" :value="$task->title" required maxlength="160" />
        <x-field name="instructions" label="Instrucciones" type="textarea" :value="$task->instructions" rows="8" maxlength="20000" class="font-mono text-sm"
                 help="En Markdown, igual que los temas: qué entregar, formato y criterios." />
        <x-field name="due_at" label="Fecha límite (opcional)" type="datetime-local" :value="$task->due_at?->timezone('America/Mexico_City')->format('Y-m-d\TH:i')" class="max-w-64"
                 help="Hora de Ciudad de México. Sin fecha, la tarea no vence." />
        <div class="grid gap-6 sm:grid-cols-3">
            <x-field name="max_score" label="Calificación máxima" type="number" :value="$task->max_score + 0" min="1" max="999" step="0.5" required />
            <x-field name="weight" label="Peso en el promedio" type="number" :value="$task->weight + 0" min="0" max="999" step="0.5" required />
            <x-field name="position" label="Posición" type="number" :value="$task->position" min="0" max="65535" required />
        </div>

        @foreach ([$checkbox('is_required', 'Tarea obligatoria', 'Las obligatorias deben entregarse para completar el tema.'),
                   $checkbox('allow_late', 'Aceptar entregas tarde', 'Si no, después de la fecha límite ya no se puede entregar.')] as $c)
            <div>
                <input type="hidden" name="{{ $c['name'] }}" value="0">
                <label class="inline-flex min-h-11 cursor-pointer items-center gap-3 font-semibold">
                    <input type="checkbox" name="{{ $c['name'] }}" value="1" class="size-5 accent-primary" aria-describedby="{{ $c['name'] }}-help" @checked(old($c['name'], $task->{$c['name']}))>
                    {{ $c['label'] }}
                </label>
                <p id="{{ $c['name'] }}-help" class="text-sm text-muted">{{ $c['help'] }}</p>
            </div>
        @endforeach

        <div class="flex flex-wrap items-center gap-4">
            <button type="submit" class="min-h-11 rounded-field bg-primary px-6 font-semibold text-canvas hover:bg-primary-hover">{{ $editing ? 'Guardar cambios' : 'Agregar tarea' }}</button>
            <a href="{{ route('topics.edit', $topic) }}" class="inline-flex min-h-11 items-center px-2 font-semibold text-primary underline">Cancelar</a>
        </div>
    </form>

    @if ($editing)
        <details class="mt-8 max-w-3xl rounded-card border border-line bg-surface p-6">
            <summary class="flex min-h-11 cursor-pointer items-center font-semibold text-danger">Borrar tarea</summary>
            <p class="mt-3">
                @if ($task->submissions_count)
                    <strong>Se borrarán también {{ $task->submissions_count === 1 ? 'su entrega' : "sus {$task->submissions_count} entregas" }}</strong>, con sus calificaciones.
                @endif
                No se puede deshacer.
            </p>
            <form method="POST" action="{{ route('tasks.destroy', $task) }}" class="mt-4">
                @csrf
                @method('DELETE')
                <button type="submit" class="min-h-11 rounded-field border border-danger px-6 font-semibold text-danger hover:bg-danger-soft">Sí, borrar la tarea</button>
            </form>
        </details>
    @endif
</x-layouts.app>
