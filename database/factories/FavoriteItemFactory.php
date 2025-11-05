<?php

namespace Database\Factories;

use App\Models\FavoriteItem;
use App\Models\Household;
use Illuminate\Database\Eloquent\Factories\Factory;

class FavoriteItemFactory extends Factory
{
    protected $model = FavoriteItem::class;

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
            'household_id' => Household::factory(),
            'name' => fake()->word(),
            'category' => fake()->optional()->randomElement($categories),
            'quantity' => fake()->optional()->numberBetween(1, 10),
            'unit' => fake()->optional()->randomElement(['kg', 'g', 'L', 'ml', 'Stück', 'Packung']),
            'usage_count' => fake()->numberBetween(1, 50),
        ];
    }
}
