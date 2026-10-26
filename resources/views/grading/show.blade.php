@php $task = $submission->task; $course = $task->topic->block->course; @endphp

<x-layouts.app :title="'Calificar: '.$task->title.' — Gaining Grades'"
               :breadcrumbs="['Inicio' => route('dashboard'), 'Cursos' => route('courses.index'), $course->title => route('blocks.index', $course), 'Entregas' => route('grading.index', $course), $submission->user->name => null]">
    <p class="text-muted">{{ $task->topic->title }}</p>
    <h1 class="font-serif text-4xl font-semibold [overflow-wrap:anywhere]">{{ $task->title }}</h1>
    <p class="mt-1 text-muted">
        Entrega de <strong class="text-ink">{{ $submission->user->name }}</strong>
        · {{ $submission->submitted_at->timezone('America/Mexico_City')->isoFormat('D MMM YYYY, H:mm') }}
        @if ($submission->isLate())<span class="ml-1 rounded-full bg-warning-soft px-2 text-sm font-semibold text-warning-ink">Tarde</span>@endif
    </p>

    {{-- Escritorio: entrega a la izquierda, calificación a la derecha; en móvil se apilan (DESIGN §8). --}}
    <div class="mt-6 grid gap-6 lg:grid-cols-[3fr_2fr]">
        <section aria-labelledby="entrega" class="rounded-card border border-line bg-surface p-6">
            <h2 id="entrega" class="font-serif text-xl font-semibold">Respuesta</h2>
            @if ($submission->body)
                <p class="mt-3 max-w-[72ch] whitespace-pre-line [overflow-wrap:anywhere]">{{ $submission->body }}</p>
            @else
                <p class="mt-3 text-muted">Sin texto.</p>
            @endif
            @if ($submission->file_path)
                <a href="{{ route('submissions.download', $submission) }}" class="mt-3 inline-flex min-h-11 items-center font-semibold text-primary underline">Descargar {{ $submission->original_name }}</a>
            @endif
            @if ($task->instructions)
                <details class="mt-6 rounded-field bg-sunken p-4">
                    <summary class="flex min-h-11 cursor-pointer items-center font-semibold">Ver las instrucciones de la tarea</summary>
                    <div class="prose-content mt-2">{!! $task->instructionsHtml() !!}</div>
                </details>
            @endif
        </section>

        <div class="space-y-6">
            <form method="POST" action="{{ route('grading.grade', $submission) }}" class="space-y-5 rounded-card border border-line bg-surface p-6" novalidate>
                @csrf
                @method('PUT')
                <h2 class="font-serif text-xl font-semibold">Calificar</h2>
                <x-field name="score" :label="'Calificación (de 0 a '.($task->max_score + 0).')'" type="number" :value="$submission->score === null ? null : $submission->score + 0" min="0" :max="$task->max_score + 0" step="0.5" required class="max-w-40" />
                <x-field name="feedback" label="Retroalimentación (la verá el alumno junto a su nota)" type="textarea" rows="5" maxlength="5000" :value="$submission->feedback" />
                <button type="submit" class="min-h-11 w-full rounded-field bg-primary px-6 font-semibold text-canvas transition-colors duration-150 hover:bg-primary-hover">Guardar calificación</button>
            </form>

            <details class="rounded-card border border-line bg-surface p-6">
                <summary class="flex min-h-11 cursor-pointer items-center font-semibold">Devolver para que la rehaga</summary>
                <form method="POST" action="{{ route('grading.return', $submission) }}" class="mt-3 space-y-4" novalidate>
                    @csrf
                    @method('PUT')
                    <x-field name="feedback" id="return-feedback" label="Qué debe corregir" type="textarea" rows="4" maxlength="5000" required />
                    <button type="submit" class="min-h-11 rounded-field border border-line-strong px-6 font-semibold hover:bg-sunken">Devolver al alumno</button>
                </form>
            </details>
        </div>
    </div>
</x-layouts.app>
