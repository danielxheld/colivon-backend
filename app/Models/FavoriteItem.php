<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FavoriteItem extends Model
{
    use HasFactory;
    protected $fillable = [
        'household_id',
        'name',
        'category',
        'quantity',
        'unit',
        'usage_count',
    ];

    protected $casts = [
        'usage_count' => 'integer',
    ];

    public function household(): BelongsTo
    {
        return $this->belongsTo(Household::class);
    }
}
