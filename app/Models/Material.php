<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Material extends Model
{
    // uploaded_by no es asignable en masa; los campos del archivo los calcula el servidor, nunca vienen del request.
    protected $fillable = ['topic_id', 'title', 'kind', 'url', 'file_path', 'original_name', 'mime_type', 'size_bytes'];

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function topic(): BelongsTo
    {
        return $this->belongsTo(Topic::class);
    }
}
