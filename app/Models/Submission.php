<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Submission extends Model
{
    // user_id, status, score, graded_by y graded_at los pone el servidor; nunca vienen del request.
    protected $fillable = ['body', 'file_path', 'original_name'];

    protected function casts(): array
    {
        return ['score' => 'decimal:2', 'submitted_at' => 'datetime', 'graded_at' => 'datetime'];
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Tarde = la primera entrega llegó después de la fecha límite (hora del servidor, UTC). */
    public function isLate(): bool
    {
        return $this->task->due_at !== null && $this->submitted_at->greaterThan($this->task->due_at);
    }

    public function percent(): ?float
    {
        return $this->score === null ? null : round($this->score / $this->task->max_score * 100, 1);
    }

    /** "Por reforzar": nota menor al umbral de config/site.php (70 %). */
    public function needsReinforcement(): bool
    {
        return $this->status === 'graded' && $this->percent() < config('site.reinforce_below_percent');
    }
}
