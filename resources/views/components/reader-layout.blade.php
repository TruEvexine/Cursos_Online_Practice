@props(['course', 'title', 'crumbs' => []])

{{-- Marco de lectura: migas según el rol y franja fija de "Vista previa" para el dueño y el super admin. --}}
@php
    $user = auth()->user();
    $preview = $user->can('update', $course);
    $base = $user->hasRole('alumno')
        ? ['Mis cursos' => route('alumno.home')]
        : ['Inicio' => route('dashboard'), 'Cursos' => route('courses.index')];
@endphp

<x-layouts.app :title="$title.' — Gaining Grades'" :breadcrumbs="$base + $crumbs">
    @if ($preview)
        <x-slot:banner>
            <div role="status" class="sticky top-0 z-20 border-b border-primary bg-primary-soft px-4 text-primary">
                <div class="flex items-center justify-between gap-x-4">
                    <p><strong>Vista previa</strong><span class="hidden sm:inline">: así ve el curso un alumno. Aquí no se guarda progreso.</span></p>
                    <a href="{{ route('blocks.index', $course) }}" class="inline-flex min-h-11 items-center font-semibold underline">Volver a editar</a>
                </div>
            </div>
        </x-slot:banner>
    @endif

    {{ $slot }}
</x-layouts.app>
