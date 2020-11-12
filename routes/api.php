<?php

use App\Http\Controllers\Categories\CategoryController;
use App\Http\Controllers\Products\ProductController;

Route::resource('categories', CategoryController::class);
Route::resource('products', ProductController::class);
