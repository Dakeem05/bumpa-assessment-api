<?php

namespace App\Http\Controllers;

use App\Helpers\BumpaAssessment;
use App\Http\Requests\User\PurchaseRequest;
use App\Services\PurchaseService;
use Exception;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

class PurchaseController extends Controller
{
    public function __construct(private readonly PurchaseService $purchaseService){}

    public function purchase(PurchaseRequest $request)
    {
        try {
            $this->purchaseService->purchase($request->validated());
            return BumpaAssessment::response(true, 'Purchase made successfully', 200);
        } catch (InvalidArgumentException $e) {
            Log::error('Purchase: Error Encountered: ' . $e->getMessage());
            return BumpaAssessment::response(false, $e->getMessage(), 400);
        } catch (Exception $e) {
            Log::error('Purchase: Error Encountered: ' . $e->getMessage());
            return BumpaAssessment::response(false, 'Failed to make purchase', 500);
        }
    }
}
