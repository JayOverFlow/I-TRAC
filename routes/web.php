<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/sample', function () {
    return view('sample-content');
});

Route::get('/register', function () {
    return view('auth/auth-cover-signup');
});

Route::get('/main', function () {
    return view('layouts/main-layout');
});