@php
    $editing = $course->exists;
    $heading = $editing ? 'Editar curso' : 'Nuevo curso';
@endphp

<x-layouts.app :title="$heading.' — Gaining Grades'"
               :breadcrumbs="['Inicio' => route('dashboard'), 'Cursos' => route('courses.index'), ($editing ? $course->title : 'Nuevo curso') => null]">
    @if ($editing)
        <h1 class="mb-4 font-serif text-3xl font-semibold">{{ $course->title }}</h1>
        <x-course-tabs :course="$course" current="details" />
    @else
        <h1 class="mb-6 font-serif text-3xl font-semibold">{{ $heading }}</h1>
    @endif

    <form method="POST" action="{{ $editing ? route('courses.update', $course) : route('courses.store') }}"
          class="max-w-2xl space-y-6 rounded-card border border-line bg-surface p-6" novalidate>
        @csrf
        @if ($editing) @method('PUT') @endif

        @if ($ponentes->isNotEmpty())
            <x-field name="ponente_id" label="Ponente" type="select" required help="El curso queda a su nombre; solo esa persona y la administración pueden editarlo.">
                <option value="">Elige un ponente</option>
                @foreach ($ponentes as $ponente)
                    <option value="{{ $ponente->id }}" @selected((string) old('ponente_id') === (string) $ponente->id)>{{ $ponente->name }} ({{ $ponente->email }})</option>
                @endforeach
            </x-field>
        @endif

        <x-field name="title" label="Título" :value="$course->title" required maxlength="160" />
        <x-field name="summary" label="Resumen" :value="$course->summary" maxlength="300" help="Una o dos frases; se muestra en la lista de cursos." />
        <x-field name="description" label="Descripción" type="textarea" :value="$course->description" maxlength="10000" rows="6" />

        <x-field name="status" label="Estado" type="select" :value="$course->status"
                 help="Solo los alumnos inscritos ven un curso publicado. Borrador y archivado quedan ocultos para ellos."
                 :options="['draft' => 'Borrador', 'published' => 'Publicado', 'archived' => 'Archivado']" />

        <div class="grid gap-6 sm:grid-cols-2">
            <x-field name="starts_on" label="Inicio (opcional)" type="date" :value="$course->starts_on?->format('Y-m-d')" />
            <x-field name="ends_on" label="Fin (opcional)" type="date" :value="$course->ends_on?->format('Y-m-d')" />
        </div>

        <x-field name="catalog_price" label="Precio en el catálogo (opcional, MXN)" type="number" min="0" max="99999" step="1" class="max-w-40"
                 :value="$course->catalog_price === null ? null : $course->catalog_price + 0"
                 help="Vacío: no aparece en el catálogo público. 0: gratis. Más de 0: los alumnos se inscriben con pago simulado (modo prueba)." />

        <div class="flex flex-wrap items-center gap-4">
            <button type="submit" class="min-h-11 rounded-field bg-primary px-6 font-semibold text-canvas hover:bg-primary-hover">
                {{ $editing ? 'Guardar cambios' : 'Crear curso' }}
            </button>
            <a href="{{ route('courses.index') }}" class="inline-flex min-h-11 items-center px-2 font-semibold text-primary underline">Cancelar</a>
        </div>
    </form>

    @if ($editing)
        @can('delete', $course)
            {{-- Confirmación sin JavaScript: el botón de borrar solo aparece al abrir el panel. --}}
            <details class="mt-8 max-w-2xl rounded-card border border-line bg-surface p-6" @error('delete') open @enderror>
                <summary class="flex min-h-11 cursor-pointer items-center font-semibold text-danger">Borrar curso</summary>
                @error('delete')
                    <p role="alert" class="mt-3 rounded-field bg-danger-soft px-4 py-3 text-danger"><span aria-hidden="true">⚠</span> {{ $message }}</p>
                @enderror
                <p class="mt-3 text-muted">Si el curso tiene alumnos inscritos o que ya lo terminaron, no se puede borrar; archívalo. Si no, desaparece de las listas.</p>
                <form method="POST" action="{{ route('courses.destroy', $course) }}" class="mt-4">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="min-h-11 rounded-field border border-danger px-6 font-semibold text-danger hover:bg-danger-soft">Sí, borrar este curso</button>
                </form>
            </details>
        @endcan
    @endif
</x-layouts.app>
