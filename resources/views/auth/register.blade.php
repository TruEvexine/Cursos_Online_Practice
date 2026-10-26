<x-layouts.app title="Crear cuenta — Gaining Grades">
    <div class="mx-auto max-w-md">
        <h1 class="mb-2 font-serif text-3xl font-semibold">Crear cuenta</h1>
        <p class="mb-6 text-muted">Cuenta de alumno para inscribirte a los cursos del <a href="{{ route('catalog.index') }}" class="font-semibold text-primary underline">catálogo</a>.</p>

        <form method="POST" action="{{ route('register') }}" class="space-y-5 rounded-card border border-line bg-surface p-6" novalidate>
            @csrf
            <x-field name="name" label="Nombre completo" required maxlength="120" autocomplete="name" />
            <x-field name="email" label="Correo" type="email" required maxlength="190" autocomplete="email" />
            <x-field name="birth_date" label="Fecha de nacimiento" type="date" required class="max-w-56"
                     help="Por ahora la cuenta es para mayores de 18 años. Solo la usamos para comprobarlo; no la guardamos." />
            <x-field name="password" label="Contraseña" type="password" required autocomplete="new-password" help="Al menos 12 caracteres." />
            <x-field name="password_confirmation" label="Repite la contraseña" type="password" required autocomplete="new-password" />

            <div>
                <label class="flex min-h-11 cursor-pointer items-start gap-3">
                    <input type="checkbox" name="terms" value="1" class="mt-1 size-5 shrink-0 accent-primary" @checked(old('terms')) @error('terms') aria-invalid="true" aria-describedby="terms-error" @enderror>
                    <span>Acepto los <a href="{{ route('terms') }}" class="font-semibold text-primary underline">términos</a> y el <a href="{{ route('privacy') }}" class="font-semibold text-primary underline">aviso de privacidad</a> (versión provisional).</span>
                </label>
                @error('terms')<p id="terms-error" class="mt-1 text-danger"><span aria-hidden="true">⚠</span> {{ $message }}</p>@enderror
            </div>

            <button type="submit" class="min-h-11 w-full rounded-field bg-primary px-4 font-semibold text-canvas transition-colors duration-150 hover:bg-primary-hover">Crear mi cuenta</button>
            <p class="text-center">¿Ya tienes cuenta? <a href="{{ route('login') }}" class="inline-flex min-h-11 items-center font-semibold text-primary underline">Inicia sesión</a></p>
        </form>
    </div>
</x-layouts.app>
