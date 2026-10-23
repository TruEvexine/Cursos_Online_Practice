@props(['materials', 'heading' => 'Materiales'])

@if ($materials->isNotEmpty())
    <section aria-labelledby="materiales" class="mt-8 max-w-[72ch]">
        <h2 id="materiales" class="mb-2 font-serif text-xl font-semibold">{{ $heading }}</h2>
        <ul class="divide-y divide-line rounded-card border border-line bg-surface">
            @foreach ($materials as $material)
                <li class="flex flex-wrap items-center justify-between gap-x-4 px-4 py-2">
                    <span class="min-w-0 [overflow-wrap:anywhere]">
                        {{ $material->title }}
                        <span class="text-sm text-muted">
                            · {{ ['file' => 'Archivo', 'link' => 'Enlace', 'video' => 'Video'][$material->kind] }}@if ($material->kind === 'file'), {{ Number::fileSize($material->size_bytes, precision: 1) }}@endif
                        </span>
                    </span>
                    @if ($material->kind === 'file')
                        <a href="{{ route('materials.download', $material) }}" class="inline-flex min-h-11 shrink-0 items-center font-semibold text-primary underline">Descargar<span class="sr-only"> «{{ $material->title }}»</span></a>
                    @else
                        <a href="{{ $material->url }}" target="_blank" rel="noopener noreferrer" class="inline-flex min-h-11 shrink-0 items-center font-semibold text-primary underline">{{ $material->kind === 'video' ? 'Ver video' : 'Abrir' }}<span class="sr-only"> «{{ $material->title }}» (se abre en otra pestaña)</span><span aria-hidden="true">&nbsp;↗</span></a>
                    @endif
                </li>
            @endforeach
        </ul>
    </section>
@endif
