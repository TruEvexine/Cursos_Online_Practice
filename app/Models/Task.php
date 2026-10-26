<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Task extends Model
{
    protected $fillable = ['title', 'instructions', 'due_at', 'max_score', 'weight', 'is_required', 'allow_late', 'position'];

    protected function casts(): array
    {
        return ['due_at' => 'datetime', 'max_score' => 'decimal:2', 'weight' => 'decimal:2', 'is_required' => 'boolean', 'allow_late' => 'boolean'];
    }

    public function topic(): BelongsTo
    {
        return $this->belongsTo(Topic::class);
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(Submission::class);
    }

    public function isPastDue(): bool
    {
        return $this->due_at !== null && now()->greaterThan($this->due_at);
    }

    /** Mismo Markdown seguro que los temas. */
    public function instructionsHtml(): string
    {
        return (new Topic(['content' => $this->instructions]))->contentHtml();
    }
}
