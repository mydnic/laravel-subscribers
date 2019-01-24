<?php

use Illuminate\Support\Facades\Route;

Route::post('subscriber', 'SubscriberController@store')->name('subscriber.store');
Route::get('subscriber/{email}/delete', 'SubscriberController@delete')->name('subscriber.delete');
