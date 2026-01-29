<?php

namespace App\Services;

use App\Enums\PurchaseStatusEnum;
use App\Models\Achievement;
use App\Models\Settings;
use App\Models\User;
use App\Events\BadgeUnlocked;
use App\Models\Badge;
use Brick\Money\Money;

class AchievementService
{
    public function getUserAchievements($email) {
        $user = User::where('email', $email)->first();

        if (!$user) {
            throw new \InvalidArgumentException('User not found');
        }

        $unlockedAchievementIds = $user->achievements()->pluck('achievement_id')->toArray();
        $unlockedAchievements = Achievement::whereIn('id', $unlockedAchievementIds)->pluck('title')->toArray();

        $nextAvailableAchievements = Achievement::whereNotIn('id', $unlockedAchievementIds)->pluck('title')->toArray();

        $latestUserBadge = $user->badges()->latest()->first();
        $currentBadge = $latestUserBadge ? Badge::find($latestUserBadge->badge_id)?->name : null;

        $nextBadge = $this->getNextAchievement($user)?->badge->name ?? null;

        $remainingToUnlockNextBadge = $this->getNextAchievement($user)?->requirement_value->getAmount()->toFloat() - $user->purchases()->where('status', PurchaseStatusEnum::SUCCESSFUL)->get()->sum(function ($purchase) {
            return $purchase->amount->getAmount()->toFloat();
        }) ?? null;

        return [
            'unlocked_achievements' => $unlockedAchievements,
            'next_available_achievements' => $nextAvailableAchievements,
            'current_badge' => $currentBadge,
            'next_badge' => $nextBadge,
            'remaining_to_unlock_next_badge' => $remainingToUnlockNextBadge,
        ];
    }

    private function getNextAchievement (User $user)
    {
        $totalPurchases = $user->purchases()->where('status', PurchaseStatusEnum::SUCCESSFUL)->get()->sum(function ($purchase) {
            return $purchase->amount->getMinorAmount()->toInt();
        });

        return Achievement::where('type', 'PURCHASE')
            ->where('requirement_type', 'TOTAL_PURCHASES')
            ->where('requirement_value', '>', $totalPurchases)
            ->orderBy('requirement_value', 'asc')
            ->with('badge')
            ->first();
    }

    public function checkAndAwardPurchaseAchievements(User $user, float $amount): void
    {
        $totalPurchases = $user->purchases()->where('status', PurchaseStatusEnum::SUCCESSFUL)->get()->sum(function ($purchase) {
            return $purchase->amount->getMinorAmount()->toInt();
        });


        $minorAmount = Money::of($amount, Settings::getValue('currency'))->getMinorAmount()->toInt();

        Achievement::where('type', 'PURCHASE')
            ->where('requirement_type', 'TOTAL_PURCHASES')
            ->where('requirement_value', '<=', $totalPurchases + $minorAmount)
            ->with('badge')
            ->chunkById(100, function ($achievements) use ($user) {
                foreach ($achievements as $achievement) {
                    $alreadyAwarded = $user->achievements()
                        ->where('achievement_id', $achievement->id)
                        ->exists();

                    if (!$alreadyAwarded) {
                        $user->achievements()->create([
                            'achievement_id' => $achievement->id,
                        ]);
                        $user->badges()->create([
                            'badge_id' => $achievement->badge->id,
                        ]);
                        event(new BadgeUnlocked($user, $achievement->badge));
                    }
                }
            });
    }
}