<?php

namespace App\Models;

use App\Traits\UUID;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Badge extends Model
{
    use HasFactory, UUID;

    protected $fillable = [
        'name',
        'icon',
        'achievement_id',
    ];

    public function achievement()
    {
        return $this->belongsTo(Achievement::class);
    }
}
