<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserChorePreference extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'chore_id',
        'preference',
        'weight',
    ];

    protected $casts = [
        'weight' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function chore(): BelongsTo
    {
        return $this->belongsTo(Chore::class);
    }

    /**
     * Get weight based on preference.
     */
    public static function getWeightForPreference(string $preference): float
    {
        return match ($preference) {
            'love' => 2.0,
            'like' => 1.5,
            'neutral' => 1.0,
            'dislike' => 0.5,
            'hate' => 0.1,
            default => 1.0,
        };
    }
}
