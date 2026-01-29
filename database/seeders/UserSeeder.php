<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'id' => Str::uuid(),
                'name' => 'John Doe',
                'email' => 'john.doe@example.com',
            ],
            [
                'id' => Str::uuid(),
                'name' => 'Jane Smith',
                'email' => 'jane.smith@example.com',
            ],
            [
                'id' => Str::uuid(),
                'name' => 'Alice Johnson',
                'email' => 'alice.johnson@example.com',
            ],
        ];

        foreach ($users as $userData) {
            User::firstOrCreate(
                ['email' => $userData['email']],
                $userData
            );
        }

        $this->command->info('Users seeded successfully!');
    }
}
