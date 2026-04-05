<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DepartmentController;

Route::group(['prefix' => 'user'], function () {
    Route::post('login',        [AuthController::class, 'login']);
    Route::post('register',     [AuthController::class, 'register']);
    Route::post('verify',       [AuthController::class, 'verify']);
    Route::post('check-tupid',  [AuthController::class, 'checkTupId']);
    Route::post('check-email',  [AuthController::class, 'checkEmail']);
    Route::post('resend-otp',   [AuthController::class, 'resendOtp']);
});

Route::get('departments', [DepartmentController::class, 'index']);
