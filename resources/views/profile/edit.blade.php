@php $limited = $user->hasRole('alumno'); @endphp

<x-layouts.app title="Mi perfil — Cursos el Mai" :breadcrumbs="['Inicio' => route('dashboard'), 'Mi perfil' => null]">
    <h1 class="mb-6 font-serif text-3xl font-semibold">Mi perfil</h1>

    <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data"
          class="max-w-2xl space-y-6 rounded-card border border-line bg-surface p-6" novalidate>
        @csrf
        @method('PUT')

        <div class="flex items-center gap-4">
            @if ($profile->avatar_path)
                <img src="{{ route('avatar', $user) }}" alt="Tu foto de perfil" class="size-20 rounded-full object-cover">
            @else
                <div class="flex size-20 items-center justify-center rounded-full bg-sunken font-serif text-2xl text-muted" aria-hidden="true">
                    {{ mb_substr($user->name, 0, 1) }}
                </div>
            @endif
            <div class="grow">
                <x-field name="avatar" label="Foto de perfil" type="file" accept=".jpg,.jpeg,.png,.webp"
                         help="JPG, PNG o WebP. Máximo 2 MB y 3000 × 3000 px." class="min-h-11 w-full py-2" />
            </div>
        </div>

        @if ($limited)
            <p class="text-muted">Nombre: <strong class="text-ink">{{ $user->name }}</strong> (solo tu ponente o la administración pueden cambiarlo).</p>
        @else
            <x-field name="name" label="Nombre" :value="$user->name" required maxlength="120" autocomplete="name" />
            <x-field name="headline" label="Título o especialidad" :value="$profile->headline" maxlength="120" />
        @endif

        <x-field name="bio" label="Sobre ti" type="textarea" :value="$profile->bio" maxlength="2000" />
        <x-field name="interests" label="Intereses" :value="$profile->interests" maxlength="255" help="Separados por comas." />

        @unless ($limited)
            <x-field name="website_url" label="Sitio web" type="url" :value="$profile->website_url" placeholder="https://" help="Solo enlaces https://" />
        @endunless

        <x-field name="accent_color" label="Color de acento" type="color" :value="$profile->accent_color ?? '#1F3A8A'" class="h-11 w-20 rounded-field border border-line-strong" />

        <p class="text-muted">Correo: {{ $user->email }}</p>

        <button type="submit" class="min-h-11 rounded-field bg-primary px-6 font-semibold text-canvas hover:bg-primary-hover">
            Guardar cambios
        </button>
    </form>
</x-layouts.app>
