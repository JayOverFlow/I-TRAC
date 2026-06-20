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
use App\Http\Controllers\CreatePoController;
use App\Http\Controllers\InventoryController;

use App\Http\Controllers\PrPreviewController;
use App\Http\Controllers\PoReviewController;
use App\Http\Controllers\DeliveryAttachmentController;
use App\Http\Controllers\Admin\AdminRolesOfficesController;
use App\Http\Controllers\Admin\AdminRolesAssignmentController;
use App\Http\Controllers\Admin\AdminActivityLogController;
use App\Http\Controllers\ProcureController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;

Route::get('/', function () {
    return view('auth/login');
});

Route::get('/sample', function () {
    return view('sample-content');
})->middleware('auth');

// Head user
Route::middleware(['auth', 'role:Head,Procurement,Supply'])->group(function () {
    Route::controller(DashboardController::class)->group(function () {
        Route::get('/dashboard', 'showDashboard')->name('show.dashboard');
    });



    Route::controller(PrPreviewController::class)->group(function () {
        Route::get('/pr-preview/{pr_id}', 'showPrPreview')->name('show.pr.preview');
    });

    Route::controller(CreateAppController::class)->group(function () {
        Route::get('/create-app/{app_id?}', 'showCreateApp')->name('show.create-app');
        Route::post('/create-app', 'createApp')->name('create.app');
    });
});

// Procurement user
Route::middleware(['auth', 'role:Procurement'])->group(function () {
    Route::controller(CreatePoController::class)->group(function () {
        Route::get('/create-po/{po_id}', 'showCreatePo')->name('show.create.po');
        Route::post('/create-po/store/{pr_id}', 'createPo')->name('create.po');
        Route::post('/create-po/update/{po_id}', 'updatePo')->name('update.po');
        Route::get('/create-po/{po_id}/export', 'exportPdf')->name('export.po.pdf');
    });

    Route::controller(ProcureController::class)->group(function () {
        Route::post('/procure/retrieve-pr', 'retrievePr')->name('procure.retrieve.pr');
    });
});

// Supply user
Route::middleware(['auth', 'role:Supply'])->group(function () {
    Route::controller(ProcureController::class)->group(function () {
        Route::post('/procure/retrieve-po', 'retrievePo')->name('procure.retrieve.po');
    });

    Route::controller(PoReviewController::class)->group(function () {
        Route::get('/po-review/{po_id}', 'showPoReview')->name('show.po.review');
        Route::post('/po-review/{po_id}/generate-attachments', 'generateAttachments')->name('generate.attachments');
    });

    Route::controller(DeliveryAttachmentController::class)->group(function () {
        Route::get('/delivery-attachment/{po_id}', 'showDeliveryAttachment')->name('show.delivery.attachment');
        Route::post('/delivery-attachment/iar/{iar_id}/save', 'saveIar')->name('save.iar');
        Route::get('/delivery-attachment/iar/{iar_id}/export', 'exportIar')->name('export.iar.pdf');
        Route::post('/delivery-attachment/ris/{ris_id}/save', 'saveRis')->name('save.ris');
        Route::get('/delivery-attachment/ris/{ris_id}/export', 'exportRis')->name('export.ris.pdf');
        Route::post('/delivery-attachment/rsmi/{rsmi_id}/save', 'saveRsmi')->name('save.rsmi');
        Route::get('/delivery-attachment/rsmi/{rsmi_id}/export', 'exportRsmi')->name('export.rsmi.pdf');
        Route::post('/delivery-attachment/ics/{ics_id}/save', 'saveIcs')->name('save.ics');
        Route::get('/delivery-attachment/ics/{ics_id}/export', 'exportIcs')->name('export.ics.pdf');
        Route::post('/delivery-attachment/rspi/{rspi_id}/save', 'saveRspi')->name('save.rspi');
        Route::get('/delivery-attachment/rspi/{rspi_id}/export', 'exportRspi')->name('export.rspi.pdf');
        Route::post('/delivery-attachment/par/{par_id}/save', 'savePar')->name('save.par');
        Route::get('/delivery-attachment/par/{par_id}/export', 'exportPar')->name('export.par.pdf');
    });

    Route::controller(InventoryController::class)->group(function () {
        Route::get('/inventory', 'showInventory')->name('show.inventory');
        // Route to generate and download the QR code item label
        Route::get('/inventory/generate-label', 'generateLabel')->name('inventory.generate-label');
        // Route::post('/inventory/upload-image', 'uploadImage')->name('inventory.upload-image');
        // Route::post('/inventory/delete-image', 'deleteImage')->name('inventory.delete-image');
        // Route::post('/po-review/{po_id}/generate-attachments', 'generateAttachments')->name('generate.attachments');
    });
});

// Procurement & Supply user
Route::middleware(['auth', 'role:Procurement,Supply'])->group(function () {
    Route::controller(ProcureController::class)->group(function () {
        Route::get('/procure', 'showProcure')->name('show.procure');
    });
});

Route::middleware('auth')->group(function () {
    // Account / Department Switching
    Route::post('/switch-account', [AuthController::class, 'switchAccount'])->name('switch.account');

    Route::get('/supply/dashboard', function () {
        return view('supply/pages/dashboard');
    });

    // Tasks
    Route::controller(TaskController::class)->group(function () {
        Route::get('/tasks', 'showTasks')->name('show.tasks');
        Route::post('/tasks/create-from-app-items', 'createFromAppItems')->name('tasks.create-from-app-items');
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

    Route::controller(MrController::class)->group(function () {
        Route::get('/mr', 'showMr')->name('show.mr');
    });

    Route::controller(CreatePrController::class)->group(function () {
        Route::get('/create-pr/{task_id}', 'showCreatePr')->name('show.create.pr');
        Route::post('/create-pr/{task_id}', 'saveDraft')->name('draft.pr');
        Route::post('/submit-pr/{task_id}', 'submitPr')->name('submit.pr');
        Route::post('/cancel-pr/{task_id}', 'cancelPr')->name('cancel.pr');
        Route::post('/create-pr/{task_id}/export', 'exportPr')->name('export.pr.from_form');
        Route::get('/create-pr/{task_id}/download-pdf', 'downloadPdf')->name('export.pr.download');
    });

    // Account Settings
    Route::controller(\App\Http\Controllers\AccountSettingsController::class)->group(function () {
        Route::get('/account-settings', 'showAccountSettings')->name('account.settings');
        Route::post('/account-settings/update-profile', 'updateProfile')->name('account.settings.update.profile');
        Route::post('/account-settings/update-password', 'updatePassword')->name('account.settings.update.password');
        Route::post('/account-settings/update-avatar', 'updateAvatar')->name('account.settings.update.avatar');
        Route::delete('/account-settings/delete-avatar', 'deleteAvatar')->name('account.settings.delete.avatar');
        Route::get('/account-settings/archive/app-data/{app_id}', 'getArchiveAppData')->name('account.settings.archive.app-data');
        Route::post('/account-settings/set-active-app', 'setActiveApp')->name('account.settings.set-active-app');
    });

    // Chat System
    Route::controller(\App\Http\Controllers\ChatController::class)->group(function () {
        Route::get('/chat/users', 'getUsers')->name('chat.users');
        Route::get('/chat/search-users', 'searchUsers')->name('chat.search');
        Route::get('/chat/messages/{userId}', 'getMessages')->name('chat.messages');
        Route::post('/chat/messages', 'sendMessage')->name('chat.send');
        Route::get('/notifications/unread-count', 'getUnreadCount')->name('notifications.unread.count');
        Route::post('/notifications/mark-read', 'markNotificationsRead')->name('notifications.mark.read');
        Route::post('/notifications/mark-single-read', 'markSingleNotificationRead')->name('notifications.mark.single.read');
    });
});

// Authentication
Route::controller(AuthController::class)->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('/login', 'showLogin')->name('login'); // Name changed to login for auth redirection
        Route::post('/login', 'login')->name('login.post');
        Route::get('/register', 'showRegister')->name('show.register');
        Route::post('/register', 'register')->name('register');
    });
    
    Route::post('/logout', 'logout')->name('logout')->middleware('auth');
});

Route::controller(EmailVerificationController::class)->group(function () {
    Route::post('/email/send-code', 'sendVerificationCode')->name('email.send-code');
    Route::post('/email/verify-code', 'verifyCode')->name('email.verify-code');
});

// Master Admin
Route::controller(AdminAuthController::class)->group(function () {
    Route::get('/admin-register', 'adminShowRegister')->name('admin.show.register');
    Route::post('/admin-register', 'adminRegister')->name('admin.register');
    Route::get('/admin-login', 'adminShowLogin')->name('admin.show.login');
    Route::post('/admin-login', 'adminLogin')->name('admin.login');
    Route::post('/admin-logout', 'adminLogout')->name('admin.logout')->middleware('admin.auth');
});

// Admin Dashboard Pages
Route::controller(\App\Http\Controllers\Admin\AdminDashboardController::class)->middleware('admin.auth')->prefix('admin')->group(function () {
    Route::get('/dashboard', 'index')->name('admin.dashboard');
    Route::post('/users/update', 'updateUser')->name('admin.users.update');
    Route::post('/users/update-password', 'updateUserPassword')->name('admin.users.update-password');
    Route::post('/users/delete', 'deleteUser')->name('admin.users.delete');
    Route::get('/roles-offices', [AdminRolesOfficesController::class, 'index'])->name('admin.roles-offices');
    Route::post('/roles-offices/save', [AdminRolesOfficesController::class, 'saveRoles'])->name('admin.roles-offices.save');
    Route::put('/roles-offices/{id}', [AdminRolesOfficesController::class, 'updateRole'])->name('admin.roles-offices.update');
    Route::delete('/roles-offices/{id}', [AdminRolesOfficesController::class, 'deleteRole'])->name('admin.roles-offices.delete');
    Route::put('/departments/{id}', [AdminRolesOfficesController::class, 'updateDepartment'])->name('admin.departments.update');
    Route::delete('/departments/{id}', [AdminRolesOfficesController::class, 'deleteDepartment'])->name('admin.departments.delete');

    Route::get('/roles-assignment', [AdminRolesAssignmentController::class, 'index'])->name('admin.roles-assignment');
    Route::post('/roles-assignment/update', [AdminRolesAssignmentController::class, 'updateRoleAssignments'])->name('admin.roles-assignment.update');
    Route::post('/roles-assignment/update-users', [AdminRolesAssignmentController::class, 'updateUserAssignments'])->name('admin.roles-assignment.update-users');
    Route::delete('/roles-assignment/user-department', [AdminRolesAssignmentController::class, 'deleteUserDepartment'])->name('admin.roles-assignment.delete-user-dept');

    Route::get('/activity-logs', [AdminActivityLogController::class, 'index'])->name('admin.activity-logs');
    Route::get('/activity-logs/latest', [AdminActivityLogController::class, 'getLatestLogs'])->name('admin.activity-logs.latest');


});


