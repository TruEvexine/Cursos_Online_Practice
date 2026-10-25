@props(['autofocus' => false])

{{-- Mismo formulario en /login y en la portada: Fortify redirige de vuelta a la página de origen con los errores. --}}
<form method="POST" action="{{ route('login') }}" {{ $attributes->merge(['class' => 'space-y-5 rounded-card border border-line bg-surface p-6']) }} novalidate>
    @csrf
    <x-field name="email" label="Correo" type="email" required :autofocus="$autofocus" autocomplete="username" />
    <x-field name="password" label="Contraseña" type="password" required autocomplete="current-password" />

    <label class="flex min-h-11 items-center gap-2">
        <input type="checkbox" name="remember" class="size-5 accent-primary"> Recordarme en este equipo
    </label>

    <button type="submit" class="min-h-11 w-full rounded-field bg-primary px-4 font-semibold text-canvas transition-colors duration-150 hover:bg-primary-hover">
        Entrar
    </button>

    <p class="text-center"><a href="{{ route('password.request') }}" class="inline-flex min-h-11 items-center text-primary underline">¿Olvidaste tu contraseña?</a></p>
</form>
