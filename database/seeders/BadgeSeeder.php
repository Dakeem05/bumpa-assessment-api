<?php

namespace Database\Seeders;

use App\Models\Achievement;
use App\Models\Badge;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BadgeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $achievements = Achievement::orderBy('requirement_value', 'asc')->get();

        if ($achievements->isEmpty()) {
            $this->command->error('No achievements found. Please run AchievementSeeder first.');
            return;
        }

        $badges = [
            [
                'name' => 'Bronze',
                'icon' => '🥉',
            ],
            [
                'name' => 'Silver',
                'icon' => '🥈',
            ],
            [
                'name' => 'Gold',
                'icon' => '🥇',
            ],
            [
                'name' => 'Diamond',
                'icon' => '💎',
            ],
        ];

        foreach ($achievements as $index => $achievement) {
            if (isset($badges[$index])) {
                Badge::firstOrCreate(
                    ['achievement_id' => $achievement->id],
                    [
                        'id' => Str::uuid(),
                        'name' => $badges[$index]['name'],
                        'icon' => $badges[$index]['icon'],
                        'achievement_id' => $achievement->id,
                    ]
                );
            }
        }

        $this->command->info('Badges seeded successfully!');
    }
}
