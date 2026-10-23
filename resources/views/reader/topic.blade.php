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
