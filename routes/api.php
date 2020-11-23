<?php

use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Categories\CategoryController;
use App\Http\Controllers\Products\ProductController;

Route::resource('categories', CategoryController::class);
Route::resource('products', ProductController::class);


Route::group(['prefix' => 'auth'], function () {
    // endpoint auth/register
    # Route::post('register', 'Auth\RegisterController@action'); # doesn't work in laravel 7
    Route::post('register', [RegisterController::class, 'action']);
});


