<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Household extends Model
{
    protected $fillable = [
        'name',
        'description',
        'invite_code',
        'owner_id',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($household) {
            if (!$household->invite_code) {
                $household->invite_code = strtoupper(Str::random(8));
            }
        });
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot('role')
            ->withTimestamps();
    }

    public function members(): BelongsToMany
    {
        return $this->users();
    }
}
