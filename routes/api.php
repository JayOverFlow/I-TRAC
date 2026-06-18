<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DepartmentController;
use App\Http\Controllers\AccountSettingsController;
use App\Http\Controllers\Api\MrApiController;

Route::group(['prefix' => 'user'], function () {
    Route::post('login',        [AuthController::class, 'login']);
    Route::post('register',     [AuthController::class, 'register']);
    Route::post('verify',       [AuthController::class, 'verify']);
    Route::post('check-tupid',  [AuthController::class, 'checkTupId']);
    Route::post('check-email',  [AuthController::class, 'checkEmail']);
    Route::post('resend-otp',   [AuthController::class, 'resendOtp']);
    Route::post('logout',       [AuthController::class, 'logout'])->middleware('auth:sanctum');
    Route::post('profile/update', [AccountSettingsController::class, 'updateProfile'])->middleware('auth:sanctum');
    Route::post('password/update', [AccountSettingsController::class, 'updatePassword'])->middleware('auth:sanctum');
    Route::post('avatar/update', [AccountSettingsController::class, 'updateAvatar'])->middleware('auth:sanctum');
    Route::post('mr/assign',    [MrApiController::class, 'assignItems'])->middleware('auth:sanctum');
    Route::get('mr/items',     [MrApiController::class, 'getUserItems'])->middleware('auth:sanctum');
    Route::post('mr/items/update-image', [MrApiController::class, 'updateItemImage'])->middleware('auth:sanctum');
});

Route::get('departments', [DepartmentController::class, 'index']);
