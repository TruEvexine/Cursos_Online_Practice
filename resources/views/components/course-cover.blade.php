@props(['course'])

{{--
    Portada generada por código (DESIGN.md, dirección visual v2): sin fotos de terceros ni licencias.
    El color y la forma del camino salen del id del curso, así que cada curso conserva siempre la suya.
    El motivo es el camino de nodos del mapa de misiones. Es decorativa: el título va en HTML.
--}}
@php
    $seed = crc32('gg-'.$course->id);
    $fill = ['#1F3A8A', '#0F766E', '#166534', '#92400E', '#334155'][$seed % 5];
    $points = [];
    foreach (range(0, 4) as $i) {
        $points[] = [30 + $i * 65, 50 + (($seed >> ($i * 3)) % 5) * 20];
    }
    $path = 'M'.implode(' L', array_map(fn ($p) => $p[0].' '.$p[1], $points));
    $done = 1 + $seed % 3; // nodos "completados" del dibujo
@endphp
<svg viewBox="0 0 320 180" preserveAspectRatio="xMidYMid slice" aria-hidden="true" focusable="false"
     {{ $attributes->merge(['class' => 'block w-full']) }}>
    <rect width="320" height="180" fill="{{ $fill }}" />
    <circle cx="{{ 250 + $seed % 40 }}" cy="{{ 20 + $seed % 30 }}" r="70" fill="#FFFFFF" fill-opacity=".07" />
    <path d="{{ $path }}" fill="none" stroke="#FFFFFF" stroke-opacity=".45" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" stroke-dasharray="1 9" />
    @foreach ($points as $i => [$x, $y])
        <circle cx="{{ $x }}" cy="{{ $y }}" r="{{ $i < $done ? 11 : 9 }}" fill="{{ $i < $done ? '#FFFFFF' : $fill }}" stroke="#FFFFFF" stroke-width="3" stroke-opacity="{{ $i < $done ? 1 : .7 }}" />
    @endforeach
</svg>
