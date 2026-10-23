@props(['course', 'current'])

{{-- En móvil "Datos del curso" se acorta a "Datos" para que las cuatro pestañas quepan sin desplazamiento. --}}
<a href="{{ route('reader.course', $course) }}" class="mb-4 inline-flex min-h-11 items-center rounded-field border border-line-strong px-4 font-semibold hover:bg-sunken">Vista previa como alumno</a>
<nav aria-label="Secciones del curso" class="mb-6 flex flex-wrap gap-x-1 border-b border-line">
    @foreach (['content' => ['Contenido', route('blocks.index', $course)], 'materials' => ['Materiales', route('materials.index', $course)], 'students' => ['Alumnos', route('students.index', $course)], 'details' => ['Datos', route('courses.edit', $course), 'del curso']] as $key => $tab)
        @php [$label, $url] = $tab; $more = $tab[2] ?? null; @endphp
        <a href="{{ $url }}" @if ($current === $key) aria-current="page" @endif
           class="-mb-px inline-flex min-h-11 items-center whitespace-nowrap border-b-2 px-2 sm:px-3 {{ $current === $key ? 'border-primary font-semibold text-primary' : 'border-transparent text-muted hover:text-ink' }}">{{ $label }}@if ($more)<span class="hidden sm:ml-1 sm:inline">{{ $more }}</span>@endif</a>
    @endforeach
</nav>
