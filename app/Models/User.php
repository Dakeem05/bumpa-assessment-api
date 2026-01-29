<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Traits\UUID;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, UUID;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
    ];

    public function wallet()
    {
        return $this->hasOne(Wallet::class);
    }

    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }

    public function achievements()
    {
        return $this->hasMany(UserAchievement::class);
    }

    public function badges()
    {
        return $this->hasMany(UserBadge::class);
    }
}
