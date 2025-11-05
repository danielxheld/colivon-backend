<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShoppingListItem extends Model
{
    use HasFactory;
    protected $fillable = [
        'shopping_list_id',
        'name',
        'quantity',
        'unit',
        'category',
        'note',
        'notes_for_shopper',
        'price',
        'actual_price',
        'shared_cost',
        'aisle_order',
        'image_url',
        'is_completed',
        'is_recurring',
        'recurrence_interval',
        'next_recurrence_date',
        'claimed_by_id',
        'bought_by_id',
        'completed_at',
    ];

    protected $casts = [
        'is_completed' => 'boolean',
        'is_recurring' => 'boolean',
        'shared_cost' => 'boolean',
        'completed_at' => 'datetime',
        'next_recurrence_date' => 'date',
        'price' => 'decimal:2',
        'actual_price' => 'decimal:2',
        'aisle_order' => 'integer',
    ];

    public function shoppingList(): BelongsTo
    {
        return $this->belongsTo(ShoppingList::class);
    }

    public function claimedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'claimed_by_id');
    }

    public function boughtBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'bought_by_id');
    }
}
