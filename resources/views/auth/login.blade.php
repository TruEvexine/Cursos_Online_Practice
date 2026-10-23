<x-layouts.app title="Iniciar sesión — Gaining Grades">
    <div class="mx-auto max-w-sm">
        <h1 class="mb-6 font-serif text-3xl font-semibold">Iniciar sesión</h1>

        <form method="POST" action="{{ route('login') }}" class="space-y-5 rounded-card border border-line bg-surface p-6" novalidate>
            @csrf
            <x-field name="email" label="Correo" type="email" required autofocus autocomplete="username" />
            <x-field name="password" label="Contraseña" type="password" required autocomplete="current-password" />

            <label class="flex items-center gap-2">
                <input type="checkbox" name="remember" class="size-4"> Recordarme en este equipo
            </label>

            <button type="submit" class="min-h-11 w-full rounded-field bg-primary px-4 font-semibold text-canvas hover:bg-primary-hover">
                Entrar
            </button>

            <p class="text-center"><a href="{{ route('password.request') }}" class="text-primary underline">¿Olvidaste tu contraseña?</a></p>
        </form>
    </div>
</x-layouts.app>
