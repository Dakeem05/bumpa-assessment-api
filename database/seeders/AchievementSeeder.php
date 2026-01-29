<?php

namespace Database\Seeders;

use App\Models\Achievement;
use Brick\Money\Money;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AchievementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $achievements = [
            [
                'id' => Str::uuid(),
                'title' => 'First Purchase',
                'type' => 'PURCHASE',
                'requirement_type' => 'TOTAL_PURCHASES',
                'requirement_value' => Money::of(10000, 'NGN')->getAmount()->toInt(), // 10k in minor units
            ],
            [
                'id' => Str::uuid(),
                'title' => 'Shopping Enthusiast',
                'type' => 'PURCHASE',
                'requirement_type' => 'TOTAL_PURCHASES',
                'requirement_value' => Money::of(25000, 'NGN')->getAmount()->toInt(), // 25k in minor units
            ],
            [
                'id' => Str::uuid(),
                'title' => 'Big Spender',
                'type' => 'PURCHASE',
                'requirement_type' => 'TOTAL_PURCHASES',
                'requirement_value' => Money::of(50000, 'NGN')->getAmount()->toInt(), // 50k in minor units
            ],
            [
                'id' => Str::uuid(),
                'title' => 'Elite Shopper',
                'type' => 'PURCHASE',
                'requirement_type' => 'TOTAL_PURCHASES',
                'requirement_value' => Money::of(100000, 'NGN')->getAmount()->toInt(), // 100k in minor units
            ],
        ];

        foreach ($achievements as $achievementData) {
            Achievement::firstOrCreate(
                [
                    'title' => $achievementData['title'],
                    'requirement_value' => $achievementData['requirement_value'],
                ],
                $achievementData
            );
        }

        $this->command->info('Achievements seeded successfully!');
    }
}
