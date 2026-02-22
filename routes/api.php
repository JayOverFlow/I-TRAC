<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController; // <-- Import the new controller

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::group(['prefix' => 'user'], function () {
    // Point to the new API controller
    Route::post('login', [AuthController::class, 'login']);

    // You will need to create 'register', 'verify', etc. methods in the 
    // new Api controllers as well, following the JSON response pattern.
    // For now, let's just fix login.
});

// ... other API routes