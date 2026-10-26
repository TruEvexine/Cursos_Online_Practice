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

    <h2 class="mt-8 mb-4 font-serif text-2xl font-semibold">{{ $progress->student ? 'Tu mapa de misiones' : 'Temario' }}</h2>
    {{-- Mapa de misiones (DESIGN §6): cada bloque es una misión y cada tema un nodo del camino. El estado siempre va también en texto. --}}
    @forelse ($course->blocks as $block)
        @php [$blockDone, $blockTotal] = $progress->blockProgress($block); @endphp
        <section aria-labelledby="bloque-{{ $block->id }}" class="mb-4 max-w-3xl rounded-card border border-line bg-surface">
            <header class="p-4 sm:px-6">
                <p class="text-sm text-muted">Misión {{ $loop->iteration }}</p>
                <h3 id="bloque-{{ $block->id }}" class="font-serif text-xl font-semibold [overflow-wrap:anywhere]">{{ $block->title }}</h3>
                @if ($block->description)
                    <p class="mt-1 max-w-[72ch] whitespace-pre-line text-muted">{{ $block->description }}</p>
                @endif
                @if ($progress->student && $blockTotal)
                    <div class="mt-3 flex items-center gap-3">
                        <progress max="{{ $blockTotal }}" value="{{ $blockDone }}" aria-labelledby="avance-{{ $block->id }}" class="mission-bar h-2 w-40"></progress>
                        <span id="avance-{{ $block->id }}" class="text-sm text-muted">{{ $blockDone }} de {{ $blockTotal }} temas obligatorios</span>
                    </div>
                @endif
            </header>
            @if ($block->topics->isNotEmpty())
                <ol class="relative px-4 pb-4 sm:px-6">
                    @foreach ($block->topics as $topic)
                        @php
                            $state = $progress->state($topic);
                            [$done, $total] = $progress->steps($topic);
                            $label = ['locked' => 'Bloqueado', 'available' => 'Disponible', 'in_progress' => "En curso: $done de $total pasos", 'completed' => 'Completado'][$state];
                        @endphp
                        <li class="relative flex gap-4 pb-3 last:pb-0">
                            @unless ($loop->last)<span aria-hidden="true" class="absolute top-10 bottom-0 left-[1.1rem] w-0.5 bg-line"></span>@endunless
                            <span aria-hidden="true" @class([
                                'relative z-10 mt-0.5 flex size-9 shrink-0 items-center justify-center rounded-full text-sm font-semibold tabular-nums',
                                'bg-sunken text-muted' => $state === 'locked',
                                'border-[3px] border-primary bg-surface text-primary' => in_array($state, ['available', 'in_progress']),
                                'bg-success-ink text-canvas' => $state === 'completed',
                            ])>
                                @if ($state === 'locked')
                                    <svg viewBox="0 0 24 24" class="size-4"><path d="M7 11V8a5 5 0 0 1 10 0v3M6 11h12v9H6z" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/></svg>
                                @elseif ($state === 'completed')
                                    <svg viewBox="0 0 24 24" class="size-5"><path d="M5 12.5l4.5 4.5L19 7.5" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                @else
                                    {{ $loop->iteration }}
                                @endif
                            </span>
                            <div class="min-w-0 py-1">
                                @if ($state === 'locked')
                                    <p aria-disabled="true" class="font-semibold text-muted [overflow-wrap:anywhere]">{{ $topic->title }}</p>
                                    <p class="text-sm text-muted">Bloqueado: completa «{{ $progress->blocker($topic)?->title }}» para desbloquearlo.</p>
                                @else
                                    <a href="{{ route('reader.topic', $topic) }}" class="inline-flex min-h-11 items-center font-semibold text-primary underline [overflow-wrap:anywhere]">{{ $topic->title }}</a>
                                    @if ($progress->student)<p class="text-sm text-muted">{{ $label }}</p>@endif
                                @endif
                                @if (! $topic->is_required || $topic->tasks->isNotEmpty())
                                    <p class="text-sm text-muted">
                                        @unless ($topic->is_required)<span class="rounded-full bg-sunken px-2">Opcional</span>@endunless
                                        @if ($topic->tasks->isNotEmpty()) {{ $topic->tasks->count() === 1 ? '1 tarea' : $topic->tasks->count().' tareas' }}@endif
                                    </p>
                                @endif
                            </div>
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
