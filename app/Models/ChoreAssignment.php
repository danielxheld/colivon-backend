<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ChoreAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'chore_id',
        'user_id',
        'assigned_at',
        'due_date',
        'status',
        'assigned_by',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'due_date' => 'date',
    ];

    public function chore(): BelongsTo
    {
        return $this->belongsTo(Chore::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function completion(): HasOne
    {
        return $this->hasOne(ChoreCompletion::class);
    }

    /**
     * Check if assignment is overdue.
     */
    public function isOverdue(): bool
    {
        return $this->status !== 'completed' && $this->due_date->isPast();
    }

    /**
     * Scope for pending assignments.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for overdue assignments.
     */
    public function scopeOverdue($query)
    {
        return $query->where('status', '!=', 'completed')
            ->where('due_date', '<', now());
    }
}
