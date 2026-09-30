<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CrmWebhookController;

Route::post('/webhooks/crm-jobs', [CrmWebhookController::class, 'handleJobWebhook'])->name('api.webhooks.crm-jobs');
Route::get('/brands/{brandIdentifier}/crm-jobs', [CrmWebhookController::class, 'getJobsForBrand'])->name('api.brands.crm-jobs');
