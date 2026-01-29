<?php

namespace App\Services\User;

use App\Models\Wallet;
use App\Models\WalletTransaction;
use Brick\Money\Money;
use Exception;
use Illuminate\Support\Facades\DB;

class WalletService
{
    public function checkBalance (Wallet $wallet, int $amount): bool
    {
        $amount = Money::of($amount, $wallet->currency);
        $starting_balance = $wallet->amount;
        $ending_balance = $starting_balance->minus($amount);

        if ($ending_balance->isGreaterThanOrEqualTo(Money::of(0, $wallet->currency)))
        {
            return true;
        }

        return false;
    }
    
    public function action(Wallet $wallet, $amount, $type)
    {
        if (!in_array($type, ['DEBIT', 'CREDIT'])) {
            throw new Exception("Invalid wallet action type: $type. Allowed types are 'DEBIT' and 'CREDIT'.");
        }
        
        DB::transaction(function () use ($wallet, $amount, $type) {

            $amount = Money::of($amount, $wallet->currency);
            
            $wallet = Wallet::where('id', $wallet->id)->lockForUpdate()->first();

            if (!$wallet) {
                throw new \Exception("Wallet not found. id: $wallet->id");
            }

            if ($wallet->amount->getCurrency() !== $amount->getCurrency()) {
                throw new \Exception("The currencies do not match. Wallet currency: {$wallet->amount->getCurrency()}, transaction currency: {$amount->getCurrency()}. User ID: {$wallet->user_id}, wallet->currency: {$wallet->currency}");
            }

            $previous_amount = $wallet->amount;
            $wallet->amount = $type === 'DEBIT' ? $wallet->amount->minus($amount) : $wallet->amount->plus($amount);
            $wallet->save();

            WalletTransaction::create([
                'wallet_id' => $wallet->id,
                'currency' => $wallet->currency,
                'type' => $type,
                'previous_balance' => $previous_amount,
                'new_balance' => $wallet->amount,
                'amount_change' => $amount
            ]);
        });
    }
}
