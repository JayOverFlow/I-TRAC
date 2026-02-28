<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\EmailVerificationController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\ImportAppController;
use App\Http\Controllers\AssignPrController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Admin\AdminRolesOfficesController;
use App\Http\Controllers\Admin\AdminRolesAssignmentController;
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
})->middleware('auth');

Route::middleware(['auth', 'role:Head,Supply'])->group(function () {

    // Grouping by Controller saves you from typing [ReportController::class, '...'] every time
    Route::controller(DashboardController::class)->group(function () {
        Route::get('/dashboard', 'showDashboard')->name('show.dashboard');
        // Route::post('/reports/store', 'store')->name('reports.store');
    });
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

// Master Admin
Route::controller(AdminAuthController::class)->group(function () {
    Route::get('/admin-register', 'adminShowRegister')->name('admin.show.register');
    Route::post('/admin-register', 'adminRegister')->name('admin.register'); // Handle submission of admin registration form
    Route::get('/admin-login', 'adminShowLogin')->name('admin.show.login');
    Route::post('/admin-login', 'adminLogin')->name('admin.login'); // Handle submission of admin login form
    Route::post('/admin-logout', 'adminLogout')->name('admin.logout')->middleware('admin.auth'); // Admin logout - protected
});

// Admin Dashboard Pages
Route::controller(\App\Http\Controllers\Admin\AdminDashboardController::class)->middleware('admin.auth')->prefix('admin')->group(function () {
    Route::get('/dashboard', 'index')->name('admin.dashboard'); // Admin dashboard (Users) - protected
    Route::get('/roles-offices', [AdminRolesOfficesController::class, 'index'])->name('admin.roles-offices'); // Admin Roles and Offices - protected
    Route::get('/roles-assignment', [AdminRolesAssignmentController::class, 'index'])->name('admin.roles-assignment'); // Admin Roles Assignment - protected
    Route::post('/roles-assignment/update', [AdminRolesAssignmentController::class, 'updateRoleAssignments'])->name('admin.roles-assignment.update');
});

// Import APP
Route::controller(ImportAppController::class)->group(function () {
    Route::get('/import-app', 'showImportApp')->name('show.import.app');
    Route::post('/import-app', 'importApp')->name('import.app');
});

Route::controller(AssignPrController::class)->group(function () {
    Route::get('/assign-pr/{app_id}', 'showAssignPR')->name('show.assign.pr');
    Route::post('/assign-pr', 'assignPR')->name('assign.pr');
});
