<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Chore extends Model
{
    use HasFactory;

    protected $fillable = [
        'household_id',
        'created_by',
        'title',
        'description',
        'category',
        'recurrence_type',
        'recurrence_interval',
        'difficulty_points',
        'estimated_duration',
        'requires_photo',
        'is_active',
        'assignment_mode',
    ];

    protected $casts = [
        'requires_photo' => 'boolean',
        'is_active' => 'boolean',
        'difficulty_points' => 'integer',
        'estimated_duration' => 'integer',
        'recurrence_interval' => 'integer',
    ];

    public function household(): BelongsTo
    {
        return $this->belongsTo(Household::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(ChoreAssignment::class);
    }

    public function preferences(): HasMany
    {
        return $this->hasMany(UserChorePreference::class);
    }

    /**
     * Get the current active assignment for this chore.
     */
    public function currentAssignment()
    {
        return $this->hasOne(ChoreAssignment::class)
            ->where('status', '!=', 'completed')
            ->latest('assigned_at');
    }
}
