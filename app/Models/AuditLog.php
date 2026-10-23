<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Bitácora: solo se inserta. Nunca guardar contraseñas, tokens ni datos sensibles en meta. */
class AuditLog extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = ['user_id', 'action', 'entity_type', 'entity_id', 'meta', 'ip'];

    protected function casts(): array
    {
        return ['meta' => 'array'];
    }

    public static function record(string $action, Model $entity, array $meta = []): self
    {
        return self::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'entity_type' => $entity->getTable(),
            'entity_id' => $entity->getKey(),
            'meta' => $meta,
            'ip' => request()->ip(),
        ]);
    }
}
