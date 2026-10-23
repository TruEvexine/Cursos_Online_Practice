{{-- PHP descartó el envío completo (post_max_size): no hay sesión ni datos del formulario, solo se puede volver. --}}
@php $back = Str::startsWith((string) request()->headers->get('referer'), url('/').'/') ? request()->headers->get('referer') : route('dashboard'); @endphp
<x-layouts.app title="Archivo demasiado grande — Gaining Grades">
    <section class="max-w-2xl rounded-card border border-line bg-surface p-6">
        <h1 class="font-serif text-3xl font-semibold">El archivo es demasiado grande</h1>
        <p class="mt-3">El servidor acepta archivos de hasta <strong>{{ App\Models\Material::maxUploadMb() }} MB</strong>. No se guardó nada.</p>
        <p class="mt-2 text-muted">Vuelve al formulario y elige un archivo más ligero (por ejemplo, comprime el PDF o reduce las imágenes).</p>
        <a href="{{ $back }}" class="mt-4 inline-flex min-h-11 items-center font-semibold text-primary underline">Volver al formulario</a>
    </section>
</x-layouts.app>
