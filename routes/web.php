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
    Route::get('projects/{project}/last-updated', [ProjectController::class, 'lastUpdated'])->name('projects.lastUpdated');
    Route::resource('deliverables', DeliverableController::class)->except(['index', 'show']);

    // Deliverable workflow transitions
    Route::post('deliverables/{deliverable}/submit', [DeliverableController::class, 'submitStage'])->name('deliverables.submit');
    Route::post('deliverables/{deliverable}/batch-revisions', [DeliverableController::class, 'batchRevisions'])->name('deliverables.batchRevisions');
    Route::post('deliverables/{deliverable}/revisions', [DeliverableController::class, 'requestRevisions'])->name('deliverables.revisions');
    Route::post('/deliverables/{deliverable}/checklist', [DeliverableController::class, 'updateChecklist'])->name('deliverables.update-checklist');

    // Deliverable exports
    Route::get('deliverables/{deliverable}/export/pdf', [DeliverableController::class, 'exportPdf'])->name('deliverables.export.pdf');
    Route::get('deliverables/{deliverable}/export/docx', [DeliverableController::class, 'exportDocx'])->name('deliverables.export.docx');
    Route::get('deliverables/{deliverable}/export/ppt', [DeliverableController::class, 'exportPpt'])->name('deliverables.export.ppt');
    
    // Batch Deliverable Exports
    Route::get('deliverables/{deliverable}/export-batch/pdf', [DeliverableController::class, 'exportBatchPdf'])->name('deliverables.export-batch.pdf');
    Route::get('deliverables/{deliverable}/export-batch/ppt', [DeliverableController::class, 'exportBatchPpt'])->name('deliverables.export-batch.ppt');

    // Admin Routes
    Route::group(['prefix' => 'admin', 'middleware' => 'admin'], function () {
        Route::get('/settings', [UserController::class, 'settings'])->name('admin.settings');
        Route::resource('users', UserController::class);
        Route::resource('subtask-types', \App\Http\Controllers\SubtaskTypeController::class)->only(['index', 'store', 'destroy']);
    });

    // Notification Actions
    Route::post('notifications/mark-all-read', function () {
        if (auth()->user()->isAdmin()) {
            \Illuminate\Notifications\DatabaseNotification::whereNull('read_at')->update(['read_at' => now()]);
        } else {
            auth()->user()->unreadNotifications->markAsRead();
        }
        return response()->json(['success' => true]);
    })->name('notifications.markAllRead');

    Route::post('notifications/archive-all', function () {
        if (auth()->user()->isAdmin()) {
            \Illuminate\Notifications\DatabaseNotification::query()->delete();
        } else {
            auth()->user()->notifications()->delete();
        }
        return response()->json(['success' => true]);
    })->name('notifications.archiveAll');
});
