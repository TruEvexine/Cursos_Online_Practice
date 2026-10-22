@props(['status'])

{{-- El estado se lee en el texto; el color solo lo refuerza. --}}
@php
    [$label, $classes] = [
        'published' => ['Publicado', 'bg-success-soft text-success-ink'],
        'draft' => ['Borrador', 'bg-sunken text-muted'],
        'archived' => ['Archivado', 'bg-warning-soft text-warning-ink'],
    ][$status];
@endphp

<span {{ $attributes->merge(['class' => "inline-block whitespace-nowrap rounded-full px-3 py-0.5 text-sm font-semibold $classes"]) }}>{{ $label }}</span>
