<?php

namespace App\Services;

use App\Models\User;
use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Support\Str;
use App\Models\WalletTransaction;
use Exception;

class TransactionService
{
    public function createTransaction(
        User $user,
        $wallet_id,
        $amount,
        $status = "SUCCESSFUL",
        $currency = 'NGN',
        $type = "PURCHASE",
        $reference = null,
    ) {
        $transaction = Transaction::create([
            "user_id" => $user->id,
            "wallet_id" => $wallet_id,
            "currency" => $currency,
            "amount" => $amount,
            "reference" => isset($reference) ? $reference  : Str::uuid(),
            "status" => $status,
            "type" => $type,
        ]);

        return $transaction;
    }

    /**
     * Update Transaction with associated Wallet Transaction
     *
     * @param Transaction $transaction
     * @param Wallet $wallet
     * @param string|null $walletTransactionId
     * @return void
     */
    public function attachWalletTransactionFor(Transaction $transaction, Wallet $wallet, ?string $walletTransactionId = null)
    {
        $walletTransaction = null;

        if (is_null($walletTransactionId)) {
            $walletTransaction = WalletTransaction::with('wallet')->latest()->first();
        } else {
            $walletTransaction = WalletTransaction::with('wallet')->find($walletTransactionId);
        }

        $walletTransactionAmountChange = $walletTransaction->amount_change->getMinorAmount()->toInt();
        $transactionAmount = $transaction->amount->getMinorAmount()->toInt();
        
        // Due diligence check to ensure that the transaction originates from the wallet
        if ($wallet->is($walletTransaction->wallet) && $wallet->is($transaction->wallet) && $walletTransactionAmountChange == $transactionAmount) {
            $this->updateTransaction($transaction, ['wallet_transaction_id' => $walletTransaction->id]);
        }
    }

    /**
     * Update a transaction with new data.
     *
     * @param Transaction $transaction
     * @param array $data
     * @return Transaction
     */
    public function updateTransaction(Transaction $transaction, array $data)
    {
        // Check if 'status' is in the data array and remove it
        $status = null;
        if (isset($data['status'])) {
            $status = $data['status'];
            unset($data['status']);
        }

        $transaction->update([
            'reference' => $data['reference'] ?? $transaction->reference,
            'wallet_id' => $data['wallet_id'] ?? $transaction->wallet_id,
            'wallet_transaction_id' => $data['wallet_transaction_id'] ?? $transaction->wallet_transaction_id,
            'amount' => $data['amount'] ?? $transaction->amount,
        ]);

        if ($status !== null) {
            $this->updateTransactionStatus($transaction, $status);
        }

        return $transaction;
    }

    /**
     * Update a transaction's status.
     *
     * @param Transaction $transaction
     * @param string $status
     * @return Transaction
     */
    public function updateTransactionStatus(Transaction $transaction, $status)
    {

        if (!in_array($status, ["SUCCESSFUL", "FAILED", "PENDING", "PROCESSING", "REVERSED"])) {
            throw new \Exception("TransactionService.updateTransactionStatus(): Invalid status: $status.");
        }

        $oldTransactionStatus = $transaction->status;

        $transaction->update([
            'status' => $status,
        ]);

        if ($status === "SUCCESSFUL" && $oldTransactionStatus !== "SUCCESSFUL") {
            // transaction state is changing to successful
            if ($transaction->isFundWalletTransaction()) {
                // Event
            }

            if ($transaction->isSendMoneyTransaction()) {
                // Event
            }
        }

        return $transaction;
    }

}
