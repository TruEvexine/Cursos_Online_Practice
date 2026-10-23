<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class Material extends Model
{
    public const MAX_KB = 10240;

    // uploaded_by no es asignable en masa; los campos del archivo los calcula el servidor, nunca vienen del request.
    protected $fillable = ['topic_id', 'title', 'kind', 'url', 'file_path', 'original_name', 'mime_type', 'size_bytes'];

    /** Lo que de verdad se puede subir: 10 MB o menos si PHP (upload_max_filesize / post_max_size) acepta menos. */
    public static function maxUploadMb(): int
    {
        return (int) min(self::MAX_KB / 1024, floor(UploadedFile::getMaxFilesize() / 1048576));
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function topic(): BelongsTo
    {
        return $this->belongsTo(Topic::class);
    }
}
