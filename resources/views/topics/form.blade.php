@php
    $course = $block->course;
    $editing = $topic->exists;
    $heading = $editing ? 'Editar tema' : 'Nuevo tema';
@endphp

<x-layouts.app :title="$heading.' — Gaining Grades'"
               :breadcrumbs="['Inicio' => route('dashboard'), 'Cursos' => route('courses.index'), $course->title => route('blocks.index', $course), $heading => null]">
    <p class="text-muted">Bloque {{ $block->position }}: {{ $block->title }}</p>
    <h1 class="mb-6 font-serif text-3xl font-semibold">{{ $heading }}</h1>

    <form method="POST" action="{{ $editing ? route('topics.update', $topic) : route('topics.store', $block) }}"
          class="max-w-3xl space-y-6 rounded-card border border-line bg-surface p-6" novalidate>
        @csrf
        @if ($editing) @method('PUT') @endif

        <x-field name="title" label="Título del tema" :value="$topic->title" required maxlength="160" />
        <x-field name="position" label="Posición dentro del bloque" type="number" :value="$topic->position" min="0" max="65535" required
                 class="max-w-40" help="Los temas se ordenan de menor a mayor." />

        <div>
            <input type="hidden" name="is_required" value="0">
            <label class="inline-flex min-h-11 cursor-pointer items-center gap-3 font-semibold">
                <input type="checkbox" name="is_required" value="1" class="size-5 accent-primary" aria-describedby="is_required-help" @checked(old('is_required', $topic->is_required))>
                Tema obligatorio
            </label>
            <p id="is_required-help" class="text-sm text-muted">Los temas obligatorios deben completarse para avanzar en el curso.</p>
        </div>

        <x-field name="content" label="Contenido" type="textarea" :value="$topic->content" rows="16" maxlength="100000" class="font-mono text-sm"
                 help="Escribe en Markdown: # títulos, **negritas**, *cursivas*, listas con guiones y enlaces [texto](https://…). El HTML no se permite y se elimina al mostrarlo." />

        <div class="flex flex-wrap items-center gap-4">
            <button type="submit" class="min-h-11 rounded-field bg-primary px-6 font-semibold text-canvas hover:bg-primary-hover">{{ $editing ? 'Guardar cambios' : 'Agregar tema' }}</button>
            <a href="{{ route('blocks.index', $course) }}" class="inline-flex min-h-11 items-center px-2 font-semibold text-primary underline">Cancelar</a>
        </div>
    </form>

    @if ($editing)
        <section aria-labelledby="tareas" class="mt-8 max-w-3xl rounded-card border border-line bg-surface p-6">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <h2 id="tareas" class="font-serif text-xl font-semibold">Tareas de este tema</h2>
                <a href="{{ route('tasks.create', $topic) }}" class="inline-flex min-h-11 items-center font-semibold text-primary underline">Nueva tarea</a>
            </div>
            @forelse ($topic->tasks as $task)
                <div class="mt-3 flex flex-wrap items-center justify-between gap-x-4 rounded-field bg-sunken px-4 py-2">
                    <div class="min-w-0">
                        <p class="font-semibold [overflow-wrap:anywhere]">{{ $task->title }} @unless ($task->is_required)<span class="text-sm font-normal text-muted">(opcional)</span>@endunless</p>
                        <p class="text-sm text-muted">
                            {{ $task->due_at ? 'Vence el '.$task->due_at->timezone('America/Mexico_City')->isoFormat('D MMM YYYY, H:mm') : 'Sin fecha límite' }}
                            · máx. {{ $task->max_score + 0 }} · peso {{ $task->weight + 0 }}
                        </p>
                    </div>
                    <a href="{{ route('tasks.edit', $task) }}" class="inline-flex min-h-11 shrink-0 items-center font-semibold text-primary underline">Editar<span class="sr-only"> «{{ $task->title }}»</span></a>
                </div>
            @empty
                <p class="mt-2 text-muted">Este tema no tiene tareas. Sin tareas, el tema se completa al marcarlo como visto.</p>
            @endforelse
        </section>

        <details class="mt-8 max-w-3xl rounded-card border border-line bg-surface p-6">
            <summary class="flex min-h-11 cursor-pointer items-center font-semibold text-danger">Borrar tema</summary>
            <p class="mt-3">Se borrará el tema con todo su contenido. No se puede deshacer.</p>
            <form method="POST" action="{{ route('topics.destroy', $topic) }}" class="mt-4">
                @csrf
                @method('DELETE')
                <button type="submit" class="min-h-11 rounded-field border border-danger px-6 font-semibold text-danger hover:bg-danger-soft">Sí, borrar el tema</button>
            </form>
        </details>
    @endif
</x-layouts.app>
