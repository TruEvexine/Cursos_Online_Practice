@php
    $kinds = ['file' => 'Archivo', 'link' => 'Enlace', 'video' => 'Video'];
    $kind = old('kind', 'file');
@endphp

<x-layouts.app :title="'Materiales: '.$course->title.' — Gaining Grades'"
               :breadcrumbs="['Inicio' => route('dashboard'), 'Cursos' => route('courses.index'), $course->title => route('blocks.index', $course), 'Materiales' => null]">
    <h1 class="mb-4 font-serif text-4xl font-semibold">{{ $course->title }}</h1>
    <x-course-tabs :course="$course" current="materials" />

    @if ($course->materials->isEmpty())
        <section class="mb-6 rounded-card border border-line bg-surface p-6">
            <h2 class="font-serif text-xl font-semibold">Este curso todavía no tiene materiales</h2>
            <p class="mt-2 max-w-[72ch] text-muted">Sube archivos (PDF, presentaciones, hojas de cálculo, imágenes) o comparte enlaces y videos. Puedes ligarlos a un tema o dejarlos para todo el curso.</p>
            <a href="#nuevo-material" class="mt-2 inline-flex min-h-11 items-center font-semibold text-primary underline">Agregar el primero</a>
        </section>
    @else
        {{-- Menos de 768 px: cada fila se vuelve tarjeta con etiquetas (docs/DESIGN.md §4). --}}
        <table class="mb-8 w-full md:rounded-card md:border md:border-line md:bg-surface">
            <thead class="sr-only md:not-sr-only">
                <tr class="border-b border-line text-left text-sm text-muted">
                    <th scope="col" class="px-4 py-3 font-semibold">Material</th>
                    <th scope="col" class="px-4 py-3 font-semibold">Tipo</th>
                    <th scope="col" class="px-4 py-3 font-semibold">Tema</th>
                    <th scope="col" class="px-4 py-3"><span class="sr-only">Acciones</span></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($course->materials as $material)
                    <tr class="mb-3 block rounded-card border border-line bg-surface p-4 md:mb-0 md:table-row md:rounded-none md:border-0 md:border-b md:p-0 md:last:border-b-0">
                        <td class="block pb-2 md:table-cell md:px-4 md:py-3">
                            <span class="font-semibold [overflow-wrap:anywhere]">{{ $material->title }}</span>
                            <p class="text-sm text-muted [overflow-wrap:anywhere]">
                                @if ($material->kind === 'file')
                                    {{ $material->original_name }} · {{ Number::fileSize($material->size_bytes, precision: 1) }}
                                @else
                                    {{ parse_url($material->url, PHP_URL_HOST) }}
                                @endif
                            </p>
                        </td>
                        <td class="flex justify-between gap-4 py-1 md:table-cell md:px-4 md:py-3"><span class="text-muted md:hidden">Tipo</span>{{ $kinds[$material->kind] }}</td>
                        <td class="flex justify-between gap-4 py-1 text-right md:table-cell md:px-4 md:py-3 md:text-left"><span class="text-muted md:hidden">Tema</span><span class="[overflow-wrap:anywhere]">{{ $material->topic?->title ?? 'Todo el curso' }}</span></td>
                        <td class="pt-2 md:table-cell md:px-4 md:py-3 md:text-right">
                            <div class="flex flex-wrap items-start gap-x-4 md:flex-nowrap md:justify-end md:whitespace-nowrap">
                                @if ($material->kind === 'file')
                                    <a href="{{ route('materials.download', $material) }}" class="inline-flex min-h-11 items-center font-semibold text-primary underline">Descargar<span class="sr-only"> «{{ $material->title }}»</span></a>
                                @else
                                    <a href="{{ $material->url }}" target="_blank" rel="noopener noreferrer" class="inline-flex min-h-11 items-center font-semibold text-primary underline">Abrir<span class="sr-only"> «{{ $material->title }}» (se abre en otra pestaña)</span></a>
                                @endif
                                <details>
                                    <summary class="flex min-h-11 cursor-pointer items-center font-semibold text-danger">Borrar<span class="sr-only"> «{{ $material->title }}»</span></summary>
                                    <form method="POST" action="{{ route('materials.destroy', $material) }}" class="pb-2">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="min-h-11 whitespace-nowrap rounded-field border border-danger px-4 font-semibold text-danger hover:bg-danger-soft">Sí, borrar</button>
                                    </form>
                                </details>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    {{-- Sin JavaScript: :has() oculta el campo que no corresponde al tipo elegido; si el navegador no lo soporta, se ven ambos. --}}
    <form id="nuevo-material" method="POST" action="{{ route('materials.store', $course) }}" enctype="multipart/form-data"
          class="group max-w-2xl space-y-6 rounded-card border border-line bg-surface p-6" novalidate>
        @csrf
        <h2 class="font-serif text-xl font-semibold">Nuevo material</h2>
        <x-field name="title" label="Título" required maxlength="160" />

        <fieldset>
            <legend class="mb-1 font-semibold">Tipo</legend>
            <div class="flex flex-wrap gap-x-6">
                @foreach ($kinds as $value => $label)
                    <label class="inline-flex min-h-11 cursor-pointer items-center gap-2">
                        <input type="radio" id="kind-{{ $value }}" name="kind" value="{{ $value }}" class="size-5 accent-primary" @checked($kind === $value)>
                        {{ $label }}
                    </label>
                @endforeach
            </div>
            @error('kind')<p class="mt-1 text-danger"><span aria-hidden="true">⚠</span> {{ $message }}</p>@enderror
        </fieldset>

        <div class="group-has-[#kind-link:checked]:hidden group-has-[#kind-video:checked]:hidden">
            <x-field name="file" label="Archivo" type="file" accept=".pdf,.docx,.pptx,.xlsx,.png,.jpg,.jpeg,.webp" class="py-2"
                     help="PDF, Word (.docx), PowerPoint (.pptx), Excel (.xlsx) o imagen PNG, JPG o WebP. Máximo {{ $maxMb }} MB." />
        </div>
        <div class="group-has-[#kind-file:checked]:hidden">
            <x-field name="url" label="Enlace" type="url" maxlength="500" placeholder="https://"
                     help="Solo enlaces que empiezan con https://. Los videos se abren en su sitio (no se incrustan)." />
        </div>

        <x-field name="topic_id" label="Tema (opcional)" type="select">
            <option value="">Todo el curso</option>
            @foreach ($course->blocks as $block)
                <optgroup label="Bloque {{ $block->position }}: {{ $block->title }}">
                    @foreach ($block->topics as $topic)
                        <option value="{{ $topic->id }}" @selected((string) old('topic_id') === (string) $topic->id)>{{ $topic->title }}</option>
                    @endforeach
                </optgroup>
            @endforeach
        </x-field>

        <button type="submit" class="min-h-11 rounded-field bg-primary px-6 font-semibold text-canvas hover:bg-primary-hover">Agregar material</button>
    </form>
</x-layouts.app>
