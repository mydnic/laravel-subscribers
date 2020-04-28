<?php

use Illuminate\Support\Facades\Route;

Route::post('subscriber', 'SubscriberController@store')->name('store');
Route::get('delete', 'SubscriberController@delete')->name('delete');
Route::get('verify/{id}/{hash}', 'SubscriberController@verify')->name('verify');
