<?php

namespace App\Http\Controllers\PaymentMethods;

use App\Http\Controllers\Controller;
use App\Http\Resources\PaymentMethodResource;
use Illuminate\Http\Request;

class PaymentMethodController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:api']);
    }

    public function index(Request $request)
    {
//        dd($request->user()->paymentMethods);
        return PaymentMethodResource::collection(
            $request->user()->paymentMethods
        );
    }
}
