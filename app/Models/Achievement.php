<?php

namespace App\Models;

use App\Casts\TXAmountCast;
use App\Traits\UUID;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Achievement extends Model
{
    use HasFactory, UUID;

    protected $fillable = [
        'title',
        'type',
        'requirement_type',
        'requirement_value',
    ];

    protected $casts = [
        'requirement_value' => TXAmountCast::class,
    ];

    public function badge()
    {
        return $this->hasOne(Badge::class);
    }
}
