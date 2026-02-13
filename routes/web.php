<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

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
    // Route::get('/register', 'showRegister')->name('show.register');
    // Route::post('/register', 'register')->name('register');
    Route::post('/logout', 'logout')->name('logout');
});