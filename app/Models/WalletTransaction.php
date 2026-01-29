<?php

namespace App\Models;

use App\Casts\TXAmountCast;
use App\Traits\UUID;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WalletTransaction extends Model
{
    use HasFactory, UUID;

    protected $fillable = [
        'wallet_id',
        'currency',
        'type',
        'previous_balance',
        'new_balance',
        'amount_change',
    ];


    protected $casts = [
        'previous_balance' => TXAmountCast::class,
        'new_balance'  => TXAmountCast::class,
        'amount_change' => TXAmountCast::class,
    ];


    public function wallet()
    {
        return $this->belongsTo(Wallet::class);
    }
}
