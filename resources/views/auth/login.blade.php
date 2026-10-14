<x-layouts.app title="Iniciar sesión — Cursos el Mai">
    <div class="mx-auto max-w-sm">
        <h1 class="mb-6 font-serif text-3xl font-semibold">Iniciar sesión</h1>

        <form method="POST" action="{{ route('login') }}" class="space-y-5 rounded-card border border-line bg-surface p-6" novalidate>
            @csrf

            @if ($errors->any())
                <div role="alert" class="rounded-field border border-danger px-3 py-2 text-danger">
                    <span aria-hidden="true">⚠</span> {{ $errors->first() }}
                </div>
            @endif

            <div>
                <label for="email" class="mb-1 block font-semibold">Correo</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                       class="min-h-11 w-full rounded-field border border-line-strong bg-surface px-3">
            </div>

            <div>
                <label for="password" class="mb-1 block font-semibold">Contraseña</label>
                <input id="password" name="password" type="password" required autocomplete="current-password"
                       class="min-h-11 w-full rounded-field border border-line-strong bg-surface px-3">
            </div>

            <label class="flex items-center gap-2">
                <input type="checkbox" name="remember" class="size-4"> Recordarme en este equipo
            </label>

            <button type="submit" class="min-h-11 w-full rounded-field bg-primary px-4 font-semibold text-canvas hover:bg-primary-hover">
                Entrar
            </button>
        </form>
    </div>
</x-layouts.app>
