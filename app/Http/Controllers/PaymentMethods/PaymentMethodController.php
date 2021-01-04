<?php

namespace App\Http\Controllers\PaymentMethods;

use App\Cart\Payments\Gateway;
use App\Http\Controllers\Controller;
use App\Http\Requests\PaymentMethods\PaymentMethodStoreRequest;
use App\Http\Resources\PaymentMethodResource;
use Illuminate\Http\Request;

class PaymentMethodController extends Controller
{
    // Inject the Gateway
    public function __construct(Gateway $gateway)
    {
        $this->middleware(['auth:api']);
        $this->gateway = $gateway;
    }

    public function index(Request $request)
    {
//        dd($request->user()->paymentMethods);
        return PaymentMethodResource::collection(
            $request->user()->paymentMethods
        );
    }

    // Store a card
    public function store(PaymentMethodStoreRequest $request)
    {
//        dd($this->gateway);
        // inject gateway depencency in our app
        // we need to know how to get user back so we use withUser
        $card = $this->gateway->withUser($request->user())
            // will create new customer/if exist then this method do nothing
            ->createCustomer()
            ->addCard($request->token);

        return new PaymentMethodResource($card);
//        dd($card);
                // we add card using stripe token we get from api
            // will return Card Model or payment method
    }
}
