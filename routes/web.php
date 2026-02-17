<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\EmailVerificationController;
use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/sample', function () {
    return view('sample-content');
});

Route::get('/head/dashboard', function () {
    return view('head/pages/dashboard');
});

Route::get('/supply/dashboard', function () {
    return view('supply/pages/dashboard');
});

Route::get('/procurement/tasks', function () {
    return view('procurement/pages/tasks');
});

Route::get('/faculty/mr', function () {
    return view('faculty/pages/mr');
});

// Authentication
Route::controller(AuthController::class)->group(function () {
    Route::get('/login', 'showLogin')->name('show.login');
    Route::post('/login', 'login')->name('login');
    Route::get('/register', 'showRegister')->name('show.register');
    Route::post('/register', 'register')->name('register');
    Route::post('/logout', 'logout')->name('logout');
});

Route::controller(EmailVerificationController::class)->group(function () {
    Route::post('/email/send-code', 'sendVerificationCode')->name('email.send-code');
    Route::post('/email/verify-code', 'verifyCode')->name('email.verify-code');
});

// Tasks
Route::controller(TaskController::class)->group(function () {
    Route::get('/tasks', 'showTasks')->name('show.tasks');
});