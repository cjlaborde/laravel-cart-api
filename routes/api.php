<?php
# http://cart-api.test/api
use App\Models\Category;

Route::get('/', function () {
    // only grab the parent
    // order by order database field
    $categories = Category::parents()->ordered()->get();
    dd($categories);
});
