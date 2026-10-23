@props(['course', 'current'])

<nav aria-label="Secciones del curso" class="mb-6 flex gap-1 border-b border-line">
    @foreach (['content' => ['Contenido', route('blocks.index', $course)], 'details' => ['Datos del curso', route('courses.edit', $course)]] as $key => [$label, $url])
        <a href="{{ $url }}" @if ($current === $key) aria-current="page" @endif
           class="-mb-px inline-flex min-h-11 items-center border-b-2 px-3 {{ $current === $key ? 'border-primary font-semibold text-primary' : 'border-transparent text-muted hover:text-ink' }}">{{ $label }}</a>
    @endforeach
</nav>
