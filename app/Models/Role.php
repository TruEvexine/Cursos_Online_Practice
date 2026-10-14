<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    // Catálogo fijo creado en schema.sql; la app no lo modifica.
    public $timestamps = false;

    protected $fillable = [];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
