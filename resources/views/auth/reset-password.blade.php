<x-layouts.app title="Nueva contraseña — Gaining Grades">
    <div class="mx-auto max-w-sm">
        <h1 class="mb-6 font-serif text-3xl font-semibold">Nueva contraseña</h1>

        <form method="POST" action="{{ route('password.update') }}" class="space-y-5 rounded-card border border-line bg-surface p-6" novalidate>
            @csrf
            <input type="hidden" name="token" value="{{ $request->route('token') }}">

            <x-field name="email" label="Correo" type="email" :value="$request->email" required autocomplete="username" />
            <x-field name="password" label="Nueva contraseña" type="password" help="Mínimo 12 caracteres." required autocomplete="new-password" />
            <x-field name="password_confirmation" label="Repite la contraseña" type="password" required autocomplete="new-password" />

            <button type="submit" class="min-h-11 w-full rounded-field bg-primary px-4 font-semibold text-canvas hover:bg-primary-hover">
                Guardar contraseña
            </button>
        </form>
    </div>
</x-layouts.app>
