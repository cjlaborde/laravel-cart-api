<?php

namespace App\Http\Controllers\Cart;

use App\Cart\Cart;
use App\Http\Controllers\Controller;
use App\Http\Requests\Cart\CartStoreRequest;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:api']);
    }

    // The middleware will only allow us to access user if that user is authenticated
    public function store(CartStoreRequest $request, Cart $cart)
    {
        $cart->add($request->products);
    }
}
