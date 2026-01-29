<?php

namespace App\Listeners;

use App\Events\BadgeUnlocked;
use App\Services\TransactionService;
use App\Services\User\WalletService;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class BadgeUnlockedListener implements ShouldQueue
{
    const CASHBACK = 300; // in major units

    public function __construct(
        public WalletService $walletService,
        public TransactionService $transactionService,
    ) {}

    public function handle(BadgeUnlocked $event): void
    {
        $user = $event->user;
        $badge = $event->badge;

        $lockAcquired = Cache::lock("badge:{$badge->id}", 10)->block(5, function () use (
            $user,
            $badge
        ) {
            try {
                $wallet = $user->wallet;
                $amount = self::CASHBACK;
                $currency = $wallet->currency;

                $this->walletService->action($wallet, $amount, 'CREDIT');

                $walletTransaction = $wallet->walletTransactions()->latest()->first();

                $transaction = $this->transactionService->createTransaction(
                    $user,
                    $wallet->id,
                    $amount,
                    'SUCCESSFUL',
                    $currency,
                    'BADGE_CASHBACK',
                    null,
                );

                $this->transactionService->attachWalletTransactionFor(
                    $transaction,
                    $wallet,
                    $walletTransaction->id
                );
            } catch (Exception $e) {
                throw $e;
            }
        });

        if (!$lockAcquired) {
            Log::warning("BadgeUnlockedListener.handle() - Failed to acquire lock for badge: {$badge->id}. This badge may have been processed already.");
        }
    }
}
