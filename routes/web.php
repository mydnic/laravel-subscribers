<?php

use Illuminate\Support\Facades\Route;
use Mydnic\Subscribers\Http\Controllers\SubscriberController;
use Mydnic\Subscribers\Http\Controllers\TrackingController;

Route::post('subscriber', [SubscriberController::class, 'store'])->name('store');
Route::get('unsubscribe/{token}', [SubscriberController::class, 'unsubscribeByToken'])->name('unsubscribe');
Route::get('verify/{id}/{hash}', [SubscriberController::class, 'verify'])->name('verify');

// Tracking routes — must be unauthenticated
Route::get('tracking/open/{token}', [TrackingController::class, 'open'])->name('tracking.open');
Route::get('tracking/click/{token}', [TrackingController::class, 'click'])->name('tracking.click');
