<?php

namespace App\Http\Controllers;

use App\Helpers\BumpaAssessment;
use App\Http\Requests\User\PurchaseRequest;
use App\Services\AchievementService;
use Exception;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

class AchievementController extends Controller
{
    public function __construct(private readonly AchievementService $achievementService){}

    public function getUserAchievements($email)
    {
        try {
            $response = $this->achievementService->getUserAchievements($email);
            return BumpaAssessment::response(true, 'Achievements retrieved successfully', 200, $response);
        } catch (InvalidArgumentException $e) {
            Log::error('Achievements: Error Encountered: ' . $e->getMessage());
            return BumpaAssessment::response(false, $e->getMessage(), 400);
        } catch (Exception $e) {
            Log::error('Achievements: Error Encountered: ' . $e->getMessage());
            return BumpaAssessment::response(false, 'Failed to retrieve achievements', 500);
        }
    }
}
