<x-layouts.public :title="'Pago simulado: '.$course->title.' — Gaining Grades'">
    <section class="mx-auto max-w-2xl px-4 py-16">
        <p role="note" class="rounded-card bg-warning-soft p-4 text-lg text-warning-ink"><strong>Pago simulado (modo prueba).</strong> No se cobra nada ni se piden datos de tarjeta: al confirmar, solo se crea tu inscripción.</p>
        <h1 class="mt-8 font-serif text-4xl font-semibold [overflow-wrap:anywhere]">{{ $course->title }}</h1>
        <p class="mt-2 text-lg text-muted">Imparte {{ $course->ponente->name }}</p>

        <dl class="mt-8 rounded-card bg-surface p-6 text-lg">
            <div class="flex justify-between gap-4"><dt>Precio del curso</dt><dd class="font-semibold tabular-nums"><x-course-price :course="$course" /></dd></div>
            <div class="mt-2 flex justify-between gap-4"><dt>Cargo real</dt><dd class="font-semibold tabular-nums">$0 (simulado)</dd></div>
        </dl>

        <form method="POST" action="{{ route('catalog.enroll', $course) }}" class="mt-8 flex flex-wrap items-center gap-4">
            @csrf
            <button type="submit" class="min-h-11 rounded-field bg-primary px-6 font-semibold text-canvas hover:bg-primary-hover">Simular pago e inscribirme</button>
            <a href="{{ route('catalog.show', $course) }}" class="inline-flex min-h-11 items-center px-2 font-semibold text-primary underline">Cancelar</a>
        </form>
    </section>
</x-layouts.public>
