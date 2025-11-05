<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChoreCompletion extends Model
{
    use HasFactory;

    protected $fillable = [
        'chore_assignment_id',
        'completed_by',
        'completed_at',
        'photo_path',
        'notes',
        'xp_earned',
    ];

    protected $casts = [
        'completed_at' => 'datetime',
        'xp_earned' => 'integer',
    ];

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(ChoreAssignment::class, 'chore_assignment_id');
    }

    public function completedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by');
    }
}
