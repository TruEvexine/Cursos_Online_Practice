<x-layouts.app title="Verifica tu correo — Cursos el Mai">
    <div class="mx-auto max-w-md">
        <h1 class="mb-2 font-serif text-3xl font-semibold">Verifica tu correo</h1>
        <p class="mb-6 text-muted">
            Te enviamos un enlace a <strong class="text-ink">{{ auth()->user()->email }}</strong>. Ábrelo para activar tu acceso.
        </p>

        @if (session('status') === 'verification-link-sent')
            <p role="status" class="mb-4"><span aria-hidden="true">✓</span> Te enviamos un enlace nuevo.</p>
        @endif

        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit" class="min-h-11 rounded-field bg-primary px-4 font-semibold text-canvas hover:bg-primary-hover">
                Reenviar enlace
            </button>
        </form>
    </div>
</x-layouts.app>
