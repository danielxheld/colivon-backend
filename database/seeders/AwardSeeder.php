<?php

namespace Database\Seeders;

use App\Models\Award;
use Illuminate\Database\Seeder;

class AwardSeeder extends Seeder
{
    public function run(): void
    {
        $awards = [
            // Bathroom Awards
            [
                'key' => 'bathroom_rookie',
                'name' => 'Badezimmer-Neuling',
                'description' => 'Putze 5 Mal das Badezimmer',
                'icon' => '🚿',
                'category' => 'bathroom',
                'rarity' => 'common',
                'criteria' => ['chore_completions' => 5, 'chore_category' => 'bathroom'],
            ],
            [
                'key' => 'bathroom_master',
                'name' => 'Badezimmer-Meister',
                'description' => 'Putze 25 Mal das Badezimmer',
                'icon' => '🛁',
                'category' => 'bathroom',
                'rarity' => 'rare',
                'criteria' => ['chore_completions' => 25, 'chore_category' => 'bathroom'],
            ],
            [
                'key' => 'bathroom_legend',
                'name' => 'Badezimmer-Legende',
                'description' => 'Putze 100 Mal das Badezimmer',
                'icon' => '✨',
                'category' => 'bathroom',
                'rarity' => 'legendary',
                'criteria' => ['chore_completions' => 100, 'chore_category' => 'bathroom'],
            ],

            // Kitchen Awards
            [
                'key' => 'kitchen_helper',
                'name' => 'Küchen-Helfer',
                'description' => 'Erledige 5 Küchen-Aufgaben',
                'icon' => '🍳',
                'category' => 'kitchen',
                'rarity' => 'common',
                'criteria' => ['chore_completions' => 5, 'chore_category' => 'kitchen'],
            ],
            [
                'key' => 'kitchen_king',
                'name' => 'Küchen-König',
                'description' => 'Erledige 25 Küchen-Aufgaben',
                'icon' => '👑',
                'category' => 'kitchen',
                'rarity' => 'rare',
                'criteria' => ['chore_completions' => 25, 'chore_category' => 'kitchen'],
            ],
            [
                'key' => 'master_chef',
                'name' => 'Meisterkoch',
                'description' => 'Erledige 100 Küchen-Aufgaben',
                'icon' => '🧑‍🍳',
                'category' => 'kitchen',
                'rarity' => 'legendary',
                'criteria' => ['chore_completions' => 100, 'chore_category' => 'kitchen'],
            ],

            // Cleaning Awards
            [
                'key' => 'cleaner_novice',
                'name' => 'Putz-Anfänger',
                'description' => 'Erledige 5 Putzaufgaben',
                'icon' => '🧹',
                'category' => 'cleaning',
                'rarity' => 'common',
                'criteria' => ['chore_completions' => 5, 'chore_category' => 'cleaning'],
            ],
            [
                'key' => 'cleaning_pro',
                'name' => 'Putz-Profi',
                'description' => 'Erledige 25 Putzaufgaben',
                'icon' => '🧽',
                'category' => 'cleaning',
                'rarity' => 'rare',
                'criteria' => ['chore_completions' => 25, 'chore_category' => 'cleaning'],
            ],
            [
                'key' => 'spotless_champion',
                'name' => 'Sauberkeits-Champion',
                'description' => 'Erledige 100 Putzaufgaben',
                'icon' => '💎',
                'category' => 'cleaning',
                'rarity' => 'legendary',
                'criteria' => ['chore_completions' => 100, 'chore_category' => 'cleaning'],
            ],

            // Laundry Awards
            [
                'key' => 'laundry_starter',
                'name' => 'Wäsche-Starter',
                'description' => 'Wasche 5 Mal Wäsche',
                'icon' => '👕',
                'category' => 'laundry',
                'rarity' => 'common',
                'criteria' => ['chore_completions' => 5, 'chore_category' => 'laundry'],
            ],
            [
                'key' => 'laundry_expert',
                'name' => 'Wäsche-Experte',
                'description' => 'Wasche 25 Mal Wäsche',
                'icon' => '🧺',
                'category' => 'laundry',
                'rarity' => 'rare',
                'criteria' => ['chore_completions' => 25, 'chore_category' => 'laundry'],
            ],

            // Trash Awards
            [
                'key' => 'trash_taker',
                'name' => 'Müll-Bringer',
                'description' => 'Bringe 10 Mal den Müll raus',
                'icon' => '🗑️',
                'category' => 'trash',
                'rarity' => 'common',
                'criteria' => ['chore_completions' => 10, 'chore_category' => 'trash'],
            ],
            [
                'key' => 'garbage_guru',
                'name' => 'Müll-Guru',
                'description' => 'Bringe 50 Mal den Müll raus',
                'icon' => '♻️',
                'category' => 'trash',
                'rarity' => 'epic',
                'criteria' => ['chore_completions' => 50, 'chore_category' => 'trash'],
            ],

            // Streak Awards
            [
                'key' => 'on_fire',
                'name' => 'On Fire!',
                'description' => 'Erreiche einen 7-Tage Streak',
                'icon' => '🔥',
                'category' => 'general',
                'rarity' => 'rare',
                'criteria' => ['streak' => 7],
            ],
            [
                'key' => 'unstoppable',
                'name' => 'Unaufhaltsam',
                'description' => 'Erreiche einen 30-Tage Streak',
                'icon' => '⚡',
                'category' => 'general',
                'rarity' => 'epic',
                'criteria' => ['streak' => 30],
            ],
            [
                'key' => 'marathon_runner',
                'name' => 'Marathon-Läufer',
                'description' => 'Erreiche einen 100-Tage Streak',
                'icon' => '🏃',
                'category' => 'general',
                'rarity' => 'legendary',
                'criteria' => ['streak' => 100],
            ],

            // Level Awards
            [
                'key' => 'level_10',
                'name' => 'Erfahrener Helfer',
                'description' => 'Erreiche Level 10',
                'icon' => '🌟',
                'category' => 'general',
                'rarity' => 'rare',
                'criteria' => ['level' => 10],
            ],
            [
                'key' => 'level_25',
                'name' => 'Haushalts-Veteran',
                'description' => 'Erreiche Level 25',
                'icon' => '💫',
                'category' => 'general',
                'rarity' => 'epic',
                'criteria' => ['level' => 25],
            ],
            [
                'key' => 'level_50',
                'name' => 'Ultimativer Haushalts-Held',
                'description' => 'Erreiche Level 50',
                'icon' => '🏆',
                'category' => 'general',
                'rarity' => 'legendary',
                'criteria' => ['level' => 50],
            ],

            // XP Awards
            [
                'key' => 'xp_1000',
                'name' => 'Fleißig',
                'description' => 'Sammle 1000 XP',
                'icon' => '📈',
                'category' => 'general',
                'rarity' => 'common',
                'criteria' => ['total_xp' => 1000],
            ],
            [
                'key' => 'xp_5000',
                'name' => 'Power-Player',
                'description' => 'Sammle 5000 XP',
                'icon' => '💪',
                'category' => 'general',
                'rarity' => 'rare',
                'criteria' => ['total_xp' => 5000],
            ],
            [
                'key' => 'xp_10000',
                'name' => 'XP-Legende',
                'description' => 'Sammle 10000 XP',
                'icon' => '🎯',
                'category' => 'general',
                'rarity' => 'legendary',
                'criteria' => ['total_xp' => 10000],
            ],

            // Completion Awards
            [
                'key' => 'first_steps',
                'name' => 'Erste Schritte',
                'description' => 'Erledige deine erste Aufgabe',
                'icon' => '🎉',
                'category' => 'general',
                'rarity' => 'common',
                'criteria' => ['chore_completions' => 1],
            ],
            [
                'key' => 'task_crusher',
                'name' => 'Aufgaben-Crusher',
                'description' => 'Erledige 50 Aufgaben',
                'icon' => '💥',
                'category' => 'general',
                'rarity' => 'epic',
                'criteria' => ['chore_completions' => 50],
            ],
            [
                'key' => 'centurion',
                'name' => 'Zenturio',
                'description' => 'Erledige 100 Aufgaben',
                'icon' => '🛡️',
                'category' => 'general',
                'rarity' => 'legendary',
                'criteria' => ['chore_completions' => 100],
            ],
        ];

        foreach ($awards as $award) {
            Award::updateOrCreate(['key' => $award['key']], $award);
        }
    }
}
