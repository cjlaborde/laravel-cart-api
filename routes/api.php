<?php

use App\Http\Controllers\Addresses\AddressController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\MeController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Cart\CartController;
use App\Http\Controllers\Categories\CategoryController;
use App\Http\Controllers\Products\ProductController;
use App\Models\ProductVariation;

//dd(App::environment());

Route::resource('categories', CategoryController::class);
Route::resource('products', ProductController::class);
Route::resource('addresses', AddressController::class);


Route::group(['prefix' => 'auth'], function () {
    // endpoint auth/register
    # Route::post('register', 'Auth\RegisterController@action'); # doesn't work in laravel 7
    // Reason we use action because is makes thing more tidy when you use a controller for a single thing
    Route::post('register', [RegisterController::class, 'action']);
    Route::post('login', [LoginController::class, 'action']);
    Route::get('me', [MeController::class, 'action']);
});

Route::resource('cart', CartController::class, [
    'parameters' => [
        'cart' => 'productVariation'
    ]
]);
