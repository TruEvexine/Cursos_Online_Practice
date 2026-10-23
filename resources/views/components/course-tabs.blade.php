@props(['course', 'current'])

<nav aria-label="Secciones del curso" class="mb-6 flex gap-1 overflow-x-auto overflow-y-hidden border-b border-line">
    @foreach (['content' => ['Contenido', route('blocks.index', $course)], 'materials' => ['Materiales', route('materials.index', $course)], 'details' => ['Datos del curso', route('courses.edit', $course)]] as $key => [$label, $url])
        <a href="{{ $url }}" @if ($current === $key) aria-current="page" @endif
           class="inline-flex min-h-11 shrink-0 items-center whitespace-nowrap border-b-2 px-3 {{ $current === $key ? 'border-primary font-semibold text-primary' : 'border-transparent text-muted hover:text-ink' }}">{{ $label }}</a>
    @endforeach
</nav>
