<?php

namespace App\Http\Requests\Cart;

use Illuminate\Foundation\Http\FormRequest;

class CartStoreRequest extends FormRequest
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
            'products' => 'required|array',
            // Each of the product id need to exist within that product variation
            // each of the id are required| and exist within the product_variation table, under the id column
            'products.*.id' => 'required|exists:product_variations,id',
            // we will not required it since default we add to database is 1
            'products.*.quantity' => 'numeric|min:1'
        ];
    }

    // Creates custom validators errors
    public function message()
    {

    }
}
