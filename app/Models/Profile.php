<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Profile extends Model
{
    protected $primaryKey = 'user_id';

    public $incrementing = false;

    protected $fillable = ['avatar_path', 'headline', 'bio', 'interests', 'accent_color', 'website_url'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
