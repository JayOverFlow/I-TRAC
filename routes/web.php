<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\EmailVerificationController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\ImportAppController;
use App\Http\Controllers\AssignPrController;
use App\Http\Controllers\CreateAppController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MrController;
use App\Http\Controllers\CreatePrController;
use App\Http\Controllers\PrReviewController;
use App\Http\Controllers\Admin\AdminRolesOfficesController;
use App\Http\Controllers\Admin\AdminRolesAssignmentController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;

Route::get('/', function () {
    return view('auth/login');
});

Route::get('/sample', function () {
    return view('sample-content');
});

Route::middleware(['auth', 'role:Head'])->group(function () {

    Route::controller(DashboardController::class)->group(function () {
        Route::get('/dashboard', 'showDashboard')->name('show.dashboard');
    });

    Route::controller(PrReviewController::class)->group(function () {
        Route::get('/pr-review/{task_id}', 'showPrReview')->name('show.pr.review');
        Route::get('/pr-review/{task_id}/edit', 'editPrReview')->name('edit.pr.review');
        Route::post('/pr-review/{task_id}/update', 'updatePrReview')->name('update.pr.review');
        Route::post('/pr-review/{task_id}/approve', 'approvePr')->name('approve.pr');
        Route::post('/pr-review/{task_id}/reject', 'rejectPr')->name('reject.pr');
        Route::post('/pr-review/{task_id}/cancel-approve', 'cancelApprovePr')->name('cancel.approve.pr');
    });
});

Route::get('/supply/dashboard', function () {
    return view('supply/pages/dashboard');
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
    Route::post('/roles-offices/save', [AdminRolesOfficesController::class, 'saveRoles'])->name('admin.roles-offices.save');
    Route::put('/roles-offices/{id}', [AdminRolesOfficesController::class, 'updateRole'])->name('admin.roles-offices.update');
    Route::delete('/roles-offices/{id}', [AdminRolesOfficesController::class, 'deleteRole'])->name('admin.roles-offices.delete');
    Route::put('/departments/{id}', [AdminRolesOfficesController::class, 'updateDepartment'])->name('admin.departments.update');
    Route::delete('/departments/{id}', [AdminRolesOfficesController::class, 'deleteDepartment'])->name('admin.departments.delete');

    Route::get('/roles-assignment', [AdminRolesAssignmentController::class, 'index'])->name('admin.roles-assignment'); // Admin Roles Assignment - protected
    Route::post('/roles-assignment/update', [AdminRolesAssignmentController::class, 'updateRoleAssignments'])->name('admin.roles-assignment.update');
});

// Import APP
Route::controller(ImportAppController::class)->group(function () {
    Route::get('/import-app', 'showImportApp')->name('show.import.app');
    Route::post('/import-app', 'importApp')->name('import.app');
});

Route::controller(AssignPrController::class)->group(function () {
    Route::get('/assign-pr/{app_id}', 'showAssignPr')->name('show.assign.pr');
    Route::post('/assign-pr', 'assignPr')->name('assign.pr');
});

Route::middleware(['auth', 'role:Head'])->group(function () {

    Route::controller(CreateAppController::class)->group(function () {
        Route::get('/create-app', 'showCreateApp')->name('show.create-app');
        Route::post('/create-app', 'createApp')->name('create.app');
    });
});

Route::controller(MrController::class)->group(function () {
    Route::get('/mr', 'showMr')->name('show.mr');
    // Route::post('/create-app', 'createApp')->name('create.app');
});

Route::controller(CreatePrController::class)->group(function () {
    Route::get('/create-pr/{task_id}', 'showCreatePr')->name('show.create.pr');
    Route::post('/create-pr/{task_id}', 'saveDraft')->name('draft.pr');
    Route::post('/submit-pr/{task_id}', 'submitPr')->name('submit.pr');
});

// TODO: Fix this route
Route::get('/account-settings', [\App\Http\Controllers\AccountSettingsController::class, 'showAccountSettings'])
    ->middleware('auth')
    ->name('account.settings');
