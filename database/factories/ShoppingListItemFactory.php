<?php

namespace Database\Factories;

use App\Models\ShoppingList;
use App\Models\ShoppingListItem;
use Illuminate\Database\Eloquent\Factories\Factory;

class ShoppingListItemFactory extends Factory
{
    protected $model = ShoppingListItem::class;

    public function definition(): array
    {
        $categories = [
            '🥬 Obst & Gemüse',
            '🥛 Milchprodukte',
            '🍞 Backwaren',
            '🍖 Fleisch & Fisch',
            '🥫 Konserven',
            '🍝 Nudeln & Reis',
            '🍫 Süßigkeiten',
            '🧴 Haushalt',
            '🧼 Drogerie',
            '🥤 Getränke',
            '🍕 Tiefkühl',
            'Other',
        ];

        return [
            'shopping_list_id' => ShoppingList::factory(),
            'name' => fake()->word(),
            'quantity' => fake()->optional()->numberBetween(1, 10),
            'unit' => fake()->optional()->randomElement(['kg', 'g', 'L', 'ml', 'Stück', 'Packung']),
            'category' => fake()->optional()->randomElement($categories),
            'note' => fake()->optional()->sentence(),
            'price' => fake()->optional()->randomFloat(2, 0.50, 50),
            'is_completed' => fake()->boolean(20), // 20% completed
            'is_recurring' => fake()->boolean(10), // 10% recurring
            'recurrence_interval' => fake()->optional()->randomElement(['daily', 'weekly', 'monthly']),
        ];
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_completed' => true,
            'completed_at' => now(),
        ]);
    }

    public function recurring(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_recurring' => true,
            'recurrence_interval' => 'weekly',
        ]);
    }
}
