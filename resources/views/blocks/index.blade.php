<x-layouts.app :title="'Contenido: '.$course->title.' — Gaining Grades'"
               :breadcrumbs="['Inicio' => route('dashboard'), 'Cursos' => route('courses.index'), $course->title => null]">
    <h1 class="mb-4 font-serif text-3xl font-semibold">{{ $course->title }}</h1>
    <x-course-tabs :course="$course" current="content" />

    @forelse ($course->blocks as $block)
        <section aria-labelledby="bloque-{{ $block->id }}" class="mb-6 rounded-card border border-line bg-surface">
            <header class="flex flex-wrap items-start justify-between gap-x-4 gap-y-2 border-b border-line p-4 sm:px-6">
                <div class="min-w-0">
                    <p class="text-sm text-muted">Bloque {{ $block->position }}</p>
                    <h2 id="bloque-{{ $block->id }}" class="font-serif text-xl font-semibold [overflow-wrap:anywhere]">{{ $block->title }}</h2>
                    @if ($block->description)<p class="mt-1 max-w-[72ch] text-muted">{{ $block->description }}</p>@endif
                </div>
                <div class="flex gap-4">
                    <a href="{{ route('topics.create', $block) }}" class="inline-flex min-h-11 items-center font-semibold text-primary underline">Agregar tema<span class="sr-only"> a «{{ $block->title }}»</span></a>
                    <a href="{{ route('blocks.edit', $block) }}" class="inline-flex min-h-11 items-center font-semibold text-primary underline">Editar bloque<span class="sr-only"> «{{ $block->title }}»</span></a>
                </div>
            </header>

            @if ($block->topics->isEmpty())
                <p class="p-4 text-muted sm:px-6">Este bloque todavía no tiene temas. <a href="{{ route('topics.create', $block) }}" class="inline-flex min-h-11 items-center font-semibold text-primary underline">Agrega el primero</a></p>
            @else
                <ol class="divide-y divide-line">
                    @foreach ($block->topics as $topic)
                        <li class="flex items-center justify-between gap-4 px-4 py-2 sm:px-6">
                            <div class="flex min-w-0 items-baseline gap-3">
                                <span class="w-6 shrink-0 text-right text-sm tabular-nums text-muted">{{ $topic->position }}.</span>
                                <span class="min-w-0 [overflow-wrap:anywhere]">
                                    {{ $topic->title }}
                                    @unless ($topic->is_required)
                                        <span class="ml-1 inline-block whitespace-nowrap rounded-full bg-sunken px-2 text-sm text-muted">Opcional</span>
                                    @endunless
                                </span>
                            </div>
                            <a href="{{ route('topics.edit', $topic) }}" class="inline-flex min-h-11 shrink-0 items-center font-semibold text-primary underline">Editar<span class="sr-only"> el tema «{{ $topic->title }}»</span></a>
                        </li>
                    @endforeach
                </ol>
            @endif
        </section>
    @empty
        <section class="mb-6 rounded-card border border-line bg-surface p-6">
            <h2 class="font-serif text-xl font-semibold">Este curso todavía no tiene bloques</h2>
            <p class="mt-2 max-w-[72ch] text-muted">Un bloque agrupa varios temas (por ejemplo, una unidad o una semana). Crea el primero con el formulario de abajo.</p>
        </section>
    @endforelse

    <form method="POST" action="{{ route('blocks.store', $course) }}" class="max-w-2xl space-y-6 rounded-card border border-line bg-surface p-6" novalidate>
        @csrf
        <h2 class="font-serif text-xl font-semibold">Nuevo bloque</h2>
        <x-field name="title" label="Título del bloque" required maxlength="160" />
        <x-field name="description" label="Resultados esperados (opcional)" type="textarea" rows="3" help="Qué podrá hacer el alumno al terminar el bloque; se muestra en el temario." maxlength="2000" />
        <x-field name="position" label="Posición" type="number" :value="$nextPosition" min="0" max="65535" required class="max-w-40"
                 help="Los bloques se ordenan de menor a mayor." />
        <button type="submit" class="min-h-11 rounded-field bg-primary px-6 font-semibold text-canvas hover:bg-primary-hover">Agregar bloque</button>
    </form>
</x-layouts.app>
