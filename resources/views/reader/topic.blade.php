<x-reader-layout :course="$course" :title="$topic->title" :crumbs="[$course->title => route('reader.course', $course), $topic->title => null]">
    <p class="text-muted">{{ $topic->block->title }}</p>
    <h1 class="font-serif text-3xl font-semibold [overflow-wrap:anywhere]">
        {{ $topic->title }}
        @unless ($topic->is_required)
            <span class="ml-1 inline-block whitespace-nowrap rounded-full bg-sunken px-2 align-middle font-sans text-sm font-normal text-muted">Opcional</span>
        @endunless
    </h1>

    @if (trim((string) $topic->content) === '')
        <p class="mt-6 text-muted">Este tema todavía no tiene contenido.</p>
    @else
        {{-- Seguro: Markdown sin HTML crudo y solo enlaces https (Topic::contentHtml). --}}
        <article class="prose-content mt-6 max-w-[72ch]">{!! $topic->contentHtml() !!}</article>
    @endif

    <x-material-list :materials="$topic->materials" />

    @if ($topic->tasks->isNotEmpty())
        <section aria-labelledby="tareas" class="mt-8 max-w-[72ch]">
            <h2 id="tareas" class="font-serif text-2xl font-semibold">{{ $topic->tasks->count() === 1 ? 'Tarea' : 'Tareas' }}</h2>
            @foreach ($topic->tasks as $task)
                @php $submission = $submissions[$task->id] ?? null; @endphp
                <article id="tarea-{{ $task->id }}" aria-labelledby="tarea-titulo-{{ $task->id }}" class="mt-4 rounded-card border border-line bg-surface p-5">
                    <div class="flex flex-wrap items-start justify-between gap-x-4 gap-y-2">
                        <h3 id="tarea-titulo-{{ $task->id }}" class="font-serif text-xl font-semibold [overflow-wrap:anywhere]">{{ $task->title }}</h3>
                        @if ($progress->student)<x-task-status :submission="$submission" :task="$task" />@endif
                    </div>
                    <p class="mt-1 text-sm text-muted">
                        {{ $task->due_at ? 'Vence el '.$task->due_at->timezone('America/Mexico_City')->isoFormat('D MMM YYYY, H:mm') : 'Sin fecha límite' }}
                        · vale {{ $task->max_score + 0 }} puntos{{ $task->is_required ? '' : ' · opcional' }}{{ $task->allow_late ? ' · acepta entregas tarde' : '' }}
                    </p>
                    @if ($task->instructions)
                        <div class="prose-content mt-3">{!! $task->instructionsHtml() !!}</div>
                    @endif

                    @if ($submission?->feedback)
                        <div class="mt-4 rounded-field bg-sunken p-4">
                            <p class="font-semibold">Retroalimentación de tu ponente</p>
                            <p class="mt-1 whitespace-pre-line">{{ $submission->feedback }}</p>
                        </div>
                    @endif

                    @php
                        $canSubmit = auth()->user()->can('participate', $course) && $submission?->status !== 'graded'
                            && (! $task->isPastDue() || $task->allow_late || $submission?->status === 'returned');
                    @endphp
                    {{-- Si todavía se puede editar, la entrega se ve dentro del formulario; si no, aquí. --}}
                    @if ($submission && ! $canSubmit)
                        <div class="mt-4">
                            <p class="font-semibold">Tu entrega</p>
                            @if ($submission->body)<p class="mt-1 whitespace-pre-line [overflow-wrap:anywhere]">{{ $submission->body }}</p>@endif
                        </div>
                    @endif
                    @if ($submission?->file_path)
                        <a href="{{ route('submissions.download', $submission) }}" class="mt-2 inline-flex min-h-11 items-center font-semibold text-primary underline">Archivo entregado: {{ $submission->original_name }}</a>
                    @endif
                    @if ($canSubmit)
                        <form method="POST" action="{{ route('submissions.store', $task) }}" enctype="multipart/form-data" class="mt-4 space-y-4" novalidate>
                            @csrf
                            <x-field name="body" :id="'body-'.$task->id" label="{{ $submission ? 'Editar tu respuesta' : 'Tu respuesta' }}" type="textarea" rows="5" maxlength="20000" :value="$submission?->body" />
                            <x-field name="file" :id="'file-'.$task->id" label="Archivo (opcional)" type="file" accept=".pdf,.docx,.pptx,.xlsx,.png,.jpg,.jpeg,.webp" class="py-2"
                                     help="PDF, Word, PowerPoint, Excel o imagen. Máximo {{ App\Models\Material::maxUploadMb() }} MB.{{ $submission?->file_path ? ' Si subes otro, reemplaza al anterior.' : '' }}" />
                            <button type="submit" class="min-h-11 rounded-field bg-primary px-6 font-semibold text-canvas transition-colors duration-150 hover:bg-primary-hover">{{ $submission ? 'Guardar cambios' : 'Entregar' }}</button>
                        </form>
                    @endif
                </article>
            @endforeach
        </section>
    @endif

    @can('participate', $course)
        @php [$done, $total] = $progress->steps($topic); @endphp
        <section aria-labelledby="progreso" class="mt-8 max-w-[72ch] rounded-card bg-sunken p-5">
            <h2 id="progreso" class="font-serif text-xl font-semibold">Tu avance en este tema</h2>
            @if ($progress->state($topic) === 'completed')
                <p class="mt-2 text-success-ink"><span aria-hidden="true">✓</span> Tema completado.</p>
            @else
                <p class="mt-2">{{ $done }} de {{ $total }} {{ $total === 1 ? 'paso' : 'pasos' }}: leerlo{{ $total > 1 ? ' y entregar sus tareas obligatorias' : '' }}.</p>
            @endif
            @if (! $progress->isSeen($topic))
                <form method="POST" action="{{ route('reader.seen', $topic) }}" class="mt-4">
                    @csrf
                    <button type="submit" class="min-h-11 rounded-field bg-primary px-6 font-semibold text-canvas transition-colors duration-150 hover:bg-primary-hover">Marcar como visto</button>
                </form>
            @else
                <p class="mt-2 text-muted"><span aria-hidden="true">✓</span> Ya lo marcaste como visto.</p>
            @endif
        </section>
    @endcan

    <nav aria-label="Temas" class="mt-10 flex max-w-[72ch] flex-wrap justify-between gap-4 border-t border-line pt-4">
        @if ($prev)
            <a href="{{ route('reader.topic', $prev) }}" class="inline-flex min-h-11 max-w-full items-center gap-2 font-semibold text-primary underline"><span aria-hidden="true">←</span><span class="[overflow-wrap:anywhere]"><span class="sr-only">Anterior: </span>{{ $prev->title }}</span></a>
        @else
            <span></span>
        @endif
        @if ($next && $progress->isOpen($next))
            <a href="{{ route('reader.topic', $next) }}" class="ml-auto inline-flex min-h-11 max-w-full items-center gap-2 text-right font-semibold text-primary underline"><span class="[overflow-wrap:anywhere]"><span class="sr-only">Siguiente: </span>{{ $next->title }}</span><span aria-hidden="true">→</span></a>
        @else
            <a href="{{ route('reader.course', $course) }}" class="ml-auto inline-flex min-h-11 items-center font-semibold text-primary underline">Volver al temario</a>
        @endif
    </nav>
</x-reader-layout>
