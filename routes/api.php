<?php

use Illuminate\Support\Facades\Route;
use Mydnic\Subscribers\Http\Controllers\Api\CampaignController;
use Mydnic\Subscribers\Http\Controllers\Api\SubscriberController;

Route::post('subscriber', SubscriberController::class)->name('store');
