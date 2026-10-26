@php $course = $block->course; @endphp

<x-layouts.app :title="'Editar bloque — Gaining Grades'"
               :breadcrumbs="['Inicio' => route('dashboard'), 'Cursos' => route('courses.index'), $course->title => route('blocks.index', $course), 'Editar bloque' => null]">
    <h1 class="mb-6 font-serif text-4xl font-semibold">Editar bloque</h1>

    <form method="POST" action="{{ route('blocks.update', $block) }}" class="max-w-2xl space-y-6 rounded-card border border-line bg-surface p-6" novalidate>
        @csrf
        @method('PUT')
        <x-field name="title" label="Título del bloque" :value="$block->title" required maxlength="160" />
        <x-field name="description" label="Resultados esperados (opcional)" type="textarea" rows="3" help="Qué podrá hacer el alumno al terminar el bloque; se muestra en el temario." :value="$block->description" maxlength="2000" />
        <x-field name="position" label="Posición" type="number" :value="$block->position" min="0" max="65535" required class="max-w-40"
                 help="Los bloques se ordenan de menor a mayor." />
        <div class="flex flex-wrap items-center gap-4">
            <button type="submit" class="min-h-11 rounded-field bg-primary px-6 font-semibold text-canvas hover:bg-primary-hover">Guardar cambios</button>
            <a href="{{ route('blocks.index', $course) }}" class="inline-flex min-h-11 items-center px-2 font-semibold text-primary underline">Cancelar</a>
        </div>
    </form>

    {{-- Confirmación sin JavaScript: el botón de borrar solo aparece al abrir el panel. --}}
    <details class="mt-8 max-w-2xl rounded-card border border-line bg-surface p-6">
        <summary class="flex min-h-11 cursor-pointer items-center font-semibold text-danger">Borrar bloque</summary>
        <p class="mt-3">
            @if ($block->topics_count)
                <strong>Se borrarán también {{ $block->topics_count === 1 ? 'su tema' : "sus {$block->topics_count} temas" }}</strong>, con todo su contenido.
            @else
                Este bloque no tiene temas.
            @endif
            No se puede deshacer.
        </p>
        <form method="POST" action="{{ route('blocks.destroy', $block) }}" class="mt-4">
            @csrf
            @method('DELETE')
            <button type="submit" class="min-h-11 rounded-field border border-danger px-6 font-semibold text-danger hover:bg-danger-soft">
                {{ $block->topics_count ? 'Sí, borrar el bloque y '.($block->topics_count === 1 ? 'su tema' : "sus {$block->topics_count} temas") : 'Sí, borrar el bloque' }}
            </button>
        </form>
    </details>
</x-layouts.app>
