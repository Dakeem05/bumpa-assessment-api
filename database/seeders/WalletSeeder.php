<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Wallet;
use App\Models\Settings;
use Brick\Money\Money;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class WalletSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();

        if ($users->isEmpty()) {
            $this->command->error('No users found. Please run UserSeeder first.');
            return;
        }

        $currency = Settings::getValue('currency') ?? 'NGN';
        $minBalance = 100000; // 100k in minor units (kobo for NGN)

        foreach ($users as $user) {
            Wallet::create([
                'id' => Str::uuid(),
                'user_id' => $user->id,
                'currency' => $currency,
                'amount' => Money::of($minBalance, $currency)->getAmount()->toInt(),
            ]);
        }

        $this->command->info('Wallets seeded successfully for all users!');
    }
}
