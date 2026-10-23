<?php

namespace App\Models;

use App\Markdown\HttpsOnlyExtension;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use League\CommonMark\Extension\ExternalLink\ExternalLinkExtension;

class Topic extends Model
{
    protected $fillable = ['title', 'content', 'position', 'is_required'];

    protected function casts(): array
    {
        return ['is_required' => 'boolean'];
    }

    /** Markdown sin HTML crudo; solo enlaces e imágenes https://; los externos se abren con rel="noopener noreferrer". */
    public function contentHtml(): string
    {
        return Str::markdown($this->content ?? '', [
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
            'external_link' => [
                'internal_hosts' => [parse_url(config('app.url'), PHP_URL_HOST)],
                'open_in_new_window' => true,
                'noopener' => 'external',
                'noreferrer' => 'external',
            ],
        ], [new HttpsOnlyExtension, new ExternalLinkExtension]);
    }

    public function block(): BelongsTo
    {
        return $this->belongsTo(Block::class);
    }

    public function materials(): HasMany
    {
        return $this->hasMany(Material::class);
    }
}
