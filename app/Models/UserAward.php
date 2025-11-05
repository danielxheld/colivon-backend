<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserAward extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'household_id',
        'award_id',
        'earned_at',
        'progress',
    ];

    protected $casts = [
        'earned_at' => 'datetime',
    ];

    /**
     * Get the user that earned this award.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the household context.
     */
    public function household(): BelongsTo
    {
        return $this->belongsTo(Household::class);
    }

    /**
     * Get the award definition.
     */
    public function award(): BelongsTo
    {
        return $this->belongsTo(Award::class);
    }
}
