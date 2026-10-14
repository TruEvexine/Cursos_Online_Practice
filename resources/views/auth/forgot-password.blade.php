<x-layouts.app title="Recuperar contraseña — Cursos el Mai">
    <div class="mx-auto max-w-sm">
        <h1 class="mb-2 font-serif text-3xl font-semibold">Recuperar contraseña</h1>
        <p class="mb-6 text-muted">Escribe tu correo y, si tienes cuenta, te enviaremos un enlace para crear una nueva contraseña.</p>

        <form method="POST" action="{{ route('password.email') }}" class="space-y-5 rounded-card border border-line bg-surface p-6" novalidate>
            @csrf
            <x-field name="email" label="Correo" type="email" required autofocus autocomplete="username" />

            <button type="submit" class="min-h-11 w-full rounded-field bg-primary px-4 font-semibold text-canvas hover:bg-primary-hover">
                Enviar enlace
            </button>

            <p class="text-center"><a href="{{ route('login') }}" class="text-primary underline">Volver a iniciar sesión</a></p>
        </form>
    </div>
</x-layouts.app>
