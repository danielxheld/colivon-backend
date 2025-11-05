<?php

namespace App\Constants;

class ChoreTemplates
{
    public static function all(): array
    {
        return [
            // Bathroom
            [
                'title' => 'Badezimmer putzen',
                'description' => 'Toilette, Waschbecken, Dusche/Badewanne reinigen',
                'category' => 'bathroom',
                'difficulty_points' => 3,
                'estimated_duration' => 30,
                'recurrence_type' => 'weekly',
                'requires_photo' => false,
            ],
            [
                'title' => 'Bad-Spiegel reinigen',
                'description' => 'Spiegel und Armaturen polieren',
                'category' => 'bathroom',
                'difficulty_points' => 1,
                'estimated_duration' => 10,
                'recurrence_type' => 'weekly',
                'requires_photo' => false,
            ],

            // Kitchen
            [
                'title' => 'Küche aufräumen',
                'description' => 'Arbeitsflächen, Herd und Spüle reinigen',
                'category' => 'kitchen',
                'difficulty_points' => 3,
                'estimated_duration' => 25,
                'recurrence_type' => 'daily',
                'requires_photo' => false,
            ],
            [
                'title' => 'Geschirrspüler ausräumen',
                'description' => 'Sauberes Geschirr wegräumen',
                'category' => 'kitchen',
                'difficulty_points' => 1,
                'estimated_duration' => 10,
                'recurrence_type' => 'daily',
                'requires_photo' => false,
            ],
            [
                'title' => 'Kühlschrank putzen',
                'description' => 'Innenraum und Fächer reinigen',
                'category' => 'kitchen',
                'difficulty_points' => 4,
                'estimated_duration' => 45,
                'recurrence_type' => 'monthly',
                'requires_photo' => false,
            ],
            [
                'title' => 'Herd & Backofen reinigen',
                'description' => 'Herdplatten und Backofen säubern',
                'category' => 'kitchen',
                'difficulty_points' => 4,
                'estimated_duration' => 40,
                'recurrence_type' => 'biweekly',
                'requires_photo' => false,
            ],

            // Cleaning
            [
                'title' => 'Staubsaugen',
                'description' => 'Alle Räume staubsaugen',
                'category' => 'cleaning',
                'difficulty_points' => 3,
                'estimated_duration' => 30,
                'recurrence_type' => 'weekly',
                'requires_photo' => false,
            ],
            [
                'title' => 'Böden wischen',
                'description' => 'Küche und Bad wischen',
                'category' => 'cleaning',
                'difficulty_points' => 3,
                'estimated_duration' => 25,
                'recurrence_type' => 'weekly',
                'requires_photo' => false,
            ],
            [
                'title' => 'Staub wischen',
                'description' => 'Regale, Schränke und Oberflächen abstauben',
                'category' => 'cleaning',
                'difficulty_points' => 2,
                'estimated_duration' => 20,
                'recurrence_type' => 'weekly',
                'requires_photo' => false,
            ],
            [
                'title' => 'Fenster putzen',
                'description' => 'Alle Fenster innen und außen reinigen',
                'category' => 'cleaning',
                'difficulty_points' => 4,
                'estimated_duration' => 60,
                'recurrence_type' => 'monthly',
                'requires_photo' => false,
            ],

            // Laundry
            [
                'title' => 'Wäsche waschen',
                'description' => 'Wäsche sortieren, waschen und aufhängen',
                'category' => 'laundry',
                'difficulty_points' => 2,
                'estimated_duration' => 15,
                'recurrence_type' => 'weekly',
                'requires_photo' => false,
            ],
            [
                'title' => 'Wäsche zusammenlegen',
                'description' => 'Trockene Wäsche zusammenlegen und verstauen',
                'category' => 'laundry',
                'difficulty_points' => 2,
                'estimated_duration' => 20,
                'recurrence_type' => 'weekly',
                'requires_photo' => false,
            ],
            [
                'title' => 'Bügeln',
                'description' => 'Hemden und Hosen bügeln',
                'category' => 'laundry',
                'difficulty_points' => 3,
                'estimated_duration' => 30,
                'recurrence_type' => 'weekly',
                'requires_photo' => false,
            ],

            // Trash
            [
                'title' => 'Müll rausbringen',
                'description' => 'Alle Mülleimer leeren und zur Tonne bringen',
                'category' => 'trash',
                'difficulty_points' => 1,
                'estimated_duration' => 5,
                'recurrence_type' => 'weekly',
                'requires_photo' => false,
            ],
            [
                'title' => 'Altpapier entsorgen',
                'description' => 'Altpapier sammeln und zur Tonne bringen',
                'category' => 'trash',
                'difficulty_points' => 1,
                'estimated_duration' => 10,
                'recurrence_type' => 'biweekly',
                'requires_photo' => false,
            ],
            [
                'title' => 'Pfandflaschen wegbringen',
                'description' => 'Leergut zum Supermarkt bringen',
                'category' => 'trash',
                'difficulty_points' => 2,
                'estimated_duration' => 15,
                'recurrence_type' => 'weekly',
                'requires_photo' => false,
            ],

            // Living
            [
                'title' => 'Wohnzimmer aufräumen',
                'description' => 'Ordnung schaffen, Kissen aufschütteln',
                'category' => 'living',
                'difficulty_points' => 2,
                'estimated_duration' => 15,
                'recurrence_type' => 'weekly',
                'requires_photo' => false,
            ],
            [
                'title' => 'Schlafzimmer aufräumen',
                'description' => 'Bett machen, Ordnung schaffen',
                'category' => 'living',
                'difficulty_points' => 1,
                'estimated_duration' => 10,
                'recurrence_type' => 'daily',
                'requires_photo' => false,
            ],

            // Outdoor
            [
                'title' => 'Balkon/Terrasse reinigen',
                'description' => 'Fegen und Möbel abwischen',
                'category' => 'outdoor',
                'difficulty_points' => 2,
                'estimated_duration' => 20,
                'recurrence_type' => 'monthly',
                'requires_photo' => false,
            ],
            [
                'title' => 'Pflanzen gießen',
                'description' => 'Alle Zimmerpflanzen gießen',
                'category' => 'outdoor',
                'difficulty_points' => 1,
                'estimated_duration' => 10,
                'recurrence_type' => 'weekly',
                'requires_photo' => false,
            ],

            // Shopping
            [
                'title' => 'Einkaufen gehen',
                'description' => 'Wocheneinkauf erledigen',
                'category' => 'shopping',
                'difficulty_points' => 3,
                'estimated_duration' => 60,
                'recurrence_type' => 'weekly',
                'requires_photo' => false,
            ],
            [
                'title' => 'Getränke besorgen',
                'description' => 'Wasser, Säfte etc. kaufen',
                'category' => 'shopping',
                'difficulty_points' => 2,
                'estimated_duration' => 20,
                'recurrence_type' => 'weekly',
                'requires_photo' => false,
            ],
        ];
    }

    public static function getByCategory(string $category): array
    {
        return array_filter(self::all(), fn($template) => $template['category'] === $category);
    }

    public static function categories(): array
    {
        return [
            'bathroom' => '🚿 Badezimmer',
            'kitchen' => '🍳 Küche',
            'cleaning' => '🧹 Putzen',
            'laundry' => '👕 Wäsche',
            'trash' => '🗑️ Müll',
            'living' => '🛋️ Wohnbereich',
            'outdoor' => '🌿 Außenbereich',
            'shopping' => '🛒 Einkaufen',
        ];
    }
}
