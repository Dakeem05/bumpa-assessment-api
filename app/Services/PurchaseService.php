<?php

namespace App\Services;

use App\Enums\PurchaseStatusEnum;
use App\Models\Purchase;
use App\Models\User;
use App\Services\User\WalletService;
use Illuminate\Support\Facades\DB;

class PurchaseService
{
    public function __construct(private readonly AchievementService $achievementService, private readonly WalletService $walletService, private readonly TransactionService $transactionService){}

    public function purchase(array $data): void
    {
        $user = User::where('email', $data['email'])->with('wallet')->first();

        if (!$user) {
            throw new \InvalidArgumentException("User not found with email: {$data['email']}");
        }

        if (!$this->walletService->checkBalance($user->wallet, $data['amount'])) {
            throw new \InvalidArgumentException("Insufficient balance in wallet for user with email: {$data['email']}");
        }

        $this->finalizePurchase($user, $data);
        
    }

    private function finalizePurchase(User $user, array $data): void
    {
        DB::transaction(function () use ($user, $data) {
            $wallet = $user->wallet;
            
            $this->walletService->action($wallet, $data['amount'], 'DEBIT');

            $transaction = $this->transactionService->createTransaction(
                $user,
                $wallet->id,
                $data['amount'],
                "SUCCESSFUL",
                $wallet->currency,
                "PURCHASE"
            );

            $walletTransaction = $wallet->walletTransactions()->latest()->first();

            if (!$walletTransaction && $walletTransaction->wallet_id != $wallet->id && $walletTransaction->amount_change->getAmount()->toFloat() != $data['amount']) {
                throw new \Exception("Wallet transaction verification failed for user with email: {$user->email}");
            }                

            $this->transactionService->attachWalletTransactionFor(
                $transaction,
                $wallet,
                $walletTransaction->id
            );
            
            $this->achievementService->checkAndAwardPurchaseAchievements($user, $data['amount']);

            $this->createPurchaseRecord([
                'user_id' => $user->id,
                'amount' => $data['amount'],
                'status' => PurchaseStatusEnum::SUCCESSFUL,
            ]);
        });
    }

    private function createPurchaseRecord(array $data): Purchase
    {
        $purchase = Purchase::create([
            'user_id' => $data['user_id'],
            'amount' => $data['amount'],
            'status' => $data['status'],
        ]);

        return $purchase->fresh();
    }
}