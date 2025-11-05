<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function households(): BelongsToMany
    {
        return $this->belongsToMany(Household::class)
            ->withPivot('role')
            ->withTimestamps();
    }

    public function ownedHouseholds(): HasMany
    {
        return $this->hasMany(Household::class, 'owner_id');
    }

    public function shoppingLists(): HasMany
    {
        return $this->hasMany(ShoppingList::class);
    }

    public function createdChores(): HasMany
    {
        return $this->hasMany(Chore::class, 'created_by');
    }

    public function choreAssignments(): HasMany
    {
        return $this->hasMany(ChoreAssignment::class);
    }

    public function choreCompletions(): HasMany
    {
        return $this->hasMany(ChoreCompletion::class, 'completed_by');
    }

    public function chorePreferences(): HasMany
    {
        return $this->hasMany(UserChorePreference::class);
    }

    public function gamificationStats(): HasMany
    {
        return $this->hasMany(GamificationStat::class);
    }
}
