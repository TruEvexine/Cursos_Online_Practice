<x-reader-layout :course="$course" title="Mis calificaciones" :crumbs="[$course->title => route('reader.course', $course), 'Calificaciones' => null]">
    <h1 class="font-serif text-3xl font-semibold">Mis calificaciones</h1>
    <p class="mt-1 text-muted">{{ $course->title }}</p>

    <section aria-labelledby="promedio" class="mt-6 max-w-3xl rounded-card bg-primary-soft p-6">
        <h2 id="promedio" class="font-semibold">Promedio ponderado</h2>
        @if ($average === null)
            <p class="mt-1 text-lg">Todavía no tienes tareas calificadas.</p>
        @else
            <p class="mt-1 font-serif text-4xl font-semibold tabular-nums">{{ $average }}<span class="text-xl"> / 100</span></p>
            <p class="mt-1 text-sm text-muted">Solo cuentan las tareas ya calificadas, cada una según su peso.</p>
        @endif
    </section>

    @foreach ($course->blocks as $block)
        @php $tasks = $block->topics->flatMap->tasks; @endphp
        @continue($tasks->isEmpty())
        <section aria-labelledby="bloque-{{ $block->id }}" class="mt-6 max-w-3xl rounded-card border border-line bg-surface p-6">
            <h2 id="bloque-{{ $block->id }}" class="font-serif text-xl font-semibold">{{ $block->title }}</h2>
            <ul class="mt-3 space-y-3">
                @foreach ($tasks as $task)
                    @php $submission = $submissions[$task->id] ?? null; @endphp
                    <li class="rounded-field bg-sunken p-4">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <a href="{{ route('reader.topic', $task->topic) }}#tarea-{{ $task->id }}" class="inline-flex min-h-11 items-center font-semibold text-primary underline [overflow-wrap:anywhere]">{{ $task->title }}</a>
                            <x-task-status :submission="$submission" :task="$task" />
                        </div>
                        @if ($submission?->feedback)
                            <p class="mt-2 whitespace-pre-line"><span class="font-semibold">Retroalimentación:</span> {{ $submission->feedback }}</p>
                        @endif
                    </li>
                @endforeach
            </ul>
        </section>
    @endforeach
</x-reader-layout>
