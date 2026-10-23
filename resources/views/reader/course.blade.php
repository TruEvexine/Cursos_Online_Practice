<x-reader-layout :course="$course" :title="$course->title" :crumbs="[$course->title => null]">
    <h1 class="font-serif text-3xl font-semibold">{{ $course->title }}</h1>
    <p class="mt-2 text-muted">
        Imparte {{ $course->ponente->name }}
        @if ($course->starts_on)
            · {{ $course->ends_on ? 'Del '.$course->starts_on->isoFormat('D MMM').' al '.$course->ends_on->isoFormat('D MMM YYYY') : 'Inicia el '.$course->starts_on->isoFormat('D MMM YYYY') }}
        @endif
    </p>
    @if ($course->description)
        <p class="mt-4 max-w-[72ch] whitespace-pre-line">{{ $course->description }}</p>
    @endif

    <h2 class="mt-8 mb-4 font-serif text-2xl font-semibold">Temario</h2>
    @forelse ($course->blocks as $block)
        <section aria-labelledby="bloque-{{ $block->id }}" class="mb-4 max-w-3xl rounded-card border border-line bg-surface">
            <header class="p-4 sm:px-6">
                <p class="text-sm text-muted">Bloque {{ $loop->iteration }}</p>
                <h3 id="bloque-{{ $block->id }}" class="font-serif text-xl font-semibold [overflow-wrap:anywhere]">{{ $block->title }}</h3>
                @if ($block->description)
                    <p class="mt-1 max-w-[72ch] whitespace-pre-line text-muted">{{ $block->description }}</p>
                @endif
            </header>
            @if ($block->topics->isNotEmpty())
                <ol class="divide-y divide-line border-t border-line">
                    @foreach ($block->topics as $topic)
                        <li>
                            <a href="{{ route('reader.topic', $topic) }}" class="flex min-h-11 items-baseline gap-3 px-4 py-2 hover:bg-sunken sm:px-6">
                                <span class="w-6 shrink-0 text-right text-sm tabular-nums text-muted">{{ $loop->iteration }}.</span>
                                <span class="min-w-0 font-semibold text-primary underline [overflow-wrap:anywhere]">{{ $topic->title }}</span>
                                @unless ($topic->is_required)
                                    <span class="ml-auto shrink-0 rounded-full bg-sunken px-2 text-sm text-muted">Opcional</span>
                                @endunless
                            </a>
                        </li>
                    @endforeach
                </ol>
            @endif
        </section>
    @empty
        <section class="max-w-3xl rounded-card border border-line bg-surface p-6">
            <p class="text-muted">Este curso todavía no tiene temas.
                @can('update', $course)
                    <a href="{{ route('blocks.index', $course) }}" class="inline-flex min-h-11 items-center font-semibold text-primary underline">Agrega el primer bloque</a>
                @endcan
            </p>
        </section>
    @endforelse

    <x-material-list :materials="$course->materials" heading="Materiales del curso" />
</x-reader-layout>
