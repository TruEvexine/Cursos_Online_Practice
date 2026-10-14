<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateProfileRequest;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ProfileController extends Controller
{
    public function edit(Request $request)
    {
        $user = $request->user();

        return view('profile.edit', ['user' => $user, 'profile' => $user->profile ?? new Profile]);
    }

    public function update(UpdateProfileRequest $request)
    {
        $user = $request->user();
        $data = $request->safe()->except(['avatar', 'name']);

        if ($request->hasFile('avatar')) {
            $data['avatar_path'] = $this->storeAvatar($request->file('avatar'));
        }

        if (! $user->hasRole('alumno')) {
            $user->update(['name' => $request->validated('name')]);
        }

        $old = $user->profile()->value('avatar_path');
        $user->profile()->updateOrCreate([], $data);

        if (isset($data['avatar_path']) && $old) {
            Storage::disk('local')->delete($old);
        }

        return redirect()->route('profile.edit')->with('status', 'Perfil actualizado.');
    }

    /** Foto de perfil: almacenamiento privado, solo para usuarios con sesión. */
    public function avatar(User $user)
    {
        $path = $user->profile?->avatar_path;
        abort_unless($path && Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->response($path, null, ['Cache-Control' => 'private, max-age=3600']);
    }

    /**
     * Recodifica a WebP de máx. 512 px: elimina metadatos (EXIF/GPS) y cualquier contenido
     * que no sea imagen. Nombre aleatorio; nunca se usa el nombre original.
     */
    private function storeAvatar(UploadedFile $file): string
    {
        $image = @imagecreatefromstring($file->get());
        if (! $image) {
            throw ValidationException::withMessages(['avatar' => 'No se pudo leer la imagen.']);
        }

        if (max(imagesx($image), imagesy($image)) > 512) {
            $image = imagesx($image) >= imagesy($image)
                ? imagescale($image, 512)
                : imagescale($image, (int) round(imagesx($image) * 512 / imagesy($image)), 512);
        }
        imagesavealpha($image, true);

        ob_start();
        imagewebp($image, null, 85);
        $path = 'avatars/'.Str::random(40).'.webp';
        Storage::disk('local')->put($path, ob_get_clean());

        return $path;
    }
}
