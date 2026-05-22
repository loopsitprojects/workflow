<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\DeliverableController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::resource('brands', BrandController::class);
    Route::post('/deliverables/{deliverable}/batch-submit', [DeliverableController::class, 'batchSubmit'])->name('deliverables.batchSubmit');
    Route::get('brands/{brand}/retainer-board', [BrandController::class, 'retainerBoard'])->name('brands.retainer-board');
    Route::resource('projects', ProjectController::class);
    Route::resource('deliverables', DeliverableController::class)->except(['index', 'show']);

    // Deliverable workflow transitions
    Route::post('deliverables/{deliverable}/submit', [DeliverableController::class, 'submitStage'])->name('deliverables.submit');
    Route::post('deliverables/{deliverable}/batch-revisions', [DeliverableController::class, 'batchRevisions'])->name('deliverables.batchRevisions');
    Route::post('deliverables/{deliverable}/revisions', [DeliverableController::class, 'requestRevisions'])->name('deliverables.revisions');

    // Admin Routes
    Route::group(['prefix' => 'admin', 'middleware' => 'admin'], function () {
        Route::get('/settings', [UserController::class, 'settings'])->name('admin.settings');
        Route::resource('users', UserController::class);
        Route::resource('subtask-types', \App\Http\Controllers\SubtaskTypeController::class)->only(['index', 'store', 'destroy']);
    });

    // Notification Actions
    Route::post('notifications/mark-all-read', function () {
        auth()->user()->unreadNotifications->markAsRead();
        return response()->json(['success' => true]);
    })->name('notifications.markAllRead');

    Route::post('notifications/archive-all', function () {
        auth()->user()->notifications()->delete();
        return response()->json(['success' => true]);
    })->name('notifications.archiveAll');
});
