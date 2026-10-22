<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Enrollment extends Model
{
    // La tabla usa enrolled_at / removed_at en lugar de created_at / updated_at.
    public $timestamps = false;

    // "Quitar" un alumno = status 'removed'; la fila se conserva con su historial.
    protected $fillable = ['user_id', 'status', 'enrolled_at', 'removed_at'];

    protected function casts(): array
    {
        return ['enrolled_at' => 'datetime', 'removed_at' => 'datetime'];
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
