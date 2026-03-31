<?php

use Illuminate\Support\Facades\Route;
use Mydnic\Kanpen\Http\Controllers\Api\SubscriberController;

Route::post('subscriber', SubscriberController::class)->name('store');
