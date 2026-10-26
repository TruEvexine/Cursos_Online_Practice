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
        @if ($next)
            <a href="{{ route('reader.topic', $next) }}" class="ml-auto inline-flex min-h-11 max-w-full items-center gap-2 text-right font-semibold text-primary underline"><span class="[overflow-wrap:anywhere]"><span class="sr-only">Siguiente: </span>{{ $next->title }}</span><span aria-hidden="true">→</span></a>
        @else
            <a href="{{ route('reader.course', $course) }}" class="ml-auto inline-flex min-h-11 items-center font-semibold text-primary underline">Volver al temario</a>
        @endif
    </nav>
</x-reader-layout>
