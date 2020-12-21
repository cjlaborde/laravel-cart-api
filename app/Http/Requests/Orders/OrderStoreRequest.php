<?php

namespace App\Http\Requests\Orders;

use App\Models\Address;
use App\Rules\ValidShippingMethod;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OrderStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'address_id' => [
                'required',
                // 'exists:addresses,id' same as below but the one below is more customizable
                // here we check if the user owns that address
                Rule::exists('addresses', 'id')->where(function ($builder) {
                    $builder->where('user_id', $this->user()->id);
                })
            ],
            'shipping_method_id' => [
                'required',
                'exists:shipping_methods,id',
                new ValidShippingMethod($this->address_id)
            ]
        ];
    }
}
