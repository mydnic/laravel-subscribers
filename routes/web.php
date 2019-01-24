<?php

use Illuminate\Support\Facades\Route;

Route::post('subscriber', 'SubscriberController@store')->name('store');
Route::get('delete', 'SubscriberController@delete')->name('delete');
