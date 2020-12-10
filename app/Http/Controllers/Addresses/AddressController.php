<?php

namespace App\Http\Controllers\Addresses;

use App\Http\Controllers\Controller;
use App\Http\Requests\Addresses\AddressStoreRequest;
use App\Http\Resources\AddressResource;
use App\Models\Address;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:api']);
    }

    public function index(Request $request)
    {
        return AddressResource::collection(
            $request->user()->addresses
        );
    }

    public function store(AddressStoreRequest $request)
    {
        // Make the address instead of create before we attach it to particular user
        $address = Address::make($request->only([
            'name', 'address_1' , 'city', 'postal_code', 'country_id'
        ]));

        $request->user()->addresses()->save($address);

        return new AddressResource(
            $address
        );
    }
}
