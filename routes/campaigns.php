<?php

use Illuminate\Support\Facades\Route;
use Mydnic\Subscribers\Http\Controllers\Api\CampaignController;

Route::apiResource('campaigns', CampaignController::class);
Route::post('campaigns/{campaign}/send', [CampaignController::class, 'send'])->name('campaigns.send');
Route::post('campaigns/{campaign}/test', [CampaignController::class, 'test'])->name('campaigns.test');
