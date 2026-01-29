<?php

use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\AchievementController;
use Illuminate\Support\Facades\Route;

Route::prefix('users')->group(function () {
    Route::post('/purchase', [PurchaseController::class, 'purchase'])->name('users.purchase');
    Route::get('/{user}/achievements', [AchievementController::class, 'getUserAchievements'])->name('users.achievements');
});