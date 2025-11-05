<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShoppingList extends Model
{
    use HasFactory;
    protected $fillable = [
        'household_id',
        'user_id',
        'name',
        'is_public',
        'store',
        'currently_shopping_by_id',
        'is_template',
        'template_name',
        'estimated_total',
        'actual_total',
        'last_sync',
    ];

    protected $casts = [
        'is_public' => 'boolean',
        'is_template' => 'boolean',
        'estimated_total' => 'decimal:2',
        'actual_total' => 'decimal:2',
        'last_sync' => 'datetime',
    ];

    public function household(): BelongsTo
    {
        return $this->belongsTo(Household::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function currentlyShoppingBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'currently_shopping_by_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(ShoppingListItem::class);
    }
}
