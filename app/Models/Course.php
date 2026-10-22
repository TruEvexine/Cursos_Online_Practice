<?php

namespace App\Models;

use Database\Factories\CourseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Course extends Model
{
    /** @use HasFactory<CourseFactory> */
    use HasFactory, SoftDeletes;

    // ponente_id no es asignable en masa: lo pone el controlador con el usuario en sesión.
    protected $fillable = ['title', 'summary', 'description', 'status', 'unlock_mode', 'starts_on', 'ends_on'];

    protected function casts(): array
    {
        return ['starts_on' => 'date', 'ends_on' => 'date'];
    }

    protected static function booted(): void
    {
        // El slug es único incluso entre cursos borrados: sufijo aleatorio en vez de consultar duplicados.
        static::creating(fn (Course $course) => $course->slug ??= Str::slug(Str::limit($course->title, 150, '')).'-'.Str::lower(Str::random(6)));
    }

    public function ponente(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ponente_id');
    }

    public function blocks(): HasMany
    {
        return $this->hasMany(Block::class)->orderBy('position')->orderBy('id');
    }

    public function materials(): HasMany
    {
        return $this->hasMany(Material::class);
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    public function hasActiveStudent(User $user): bool
    {
        return $this->enrollments()->where('user_id', $user->id)->where('status', 'active')->exists();
    }
}
