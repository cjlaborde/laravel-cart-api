<?php

namespace App\Http\Resources;

use App\Models\ProductVariationType;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

class ProductVariationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array|\Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function toArray($request)
    {
        /**
         * When we use product variation result we saying this->id but that doesn't work since we are trying
         * to look for a key on a collection, is not expecting something to be grouped by something else
         * What we can do is check, $this->resource and check if it an instance of Collection
         * We want to return ProductVariationResource, but we want to return collection for each.
         * What is happening here is that if we are grouping items each of them groups will be an individual collection
         * with items inside of themselves
         *
         * What Resource trying to do without this if statement is trying to access $this->id which will not work
         * What the if statement does is goes into each of the keys keys that we group by and then go ahead and
         * return a collection of products variation
         */
        if ($this->resource instanceof Collection) {
            return ProductVariationResource::collection($this->resource);
        }
        return [
            'id' => $this->id,
            'name' => $this->name,
            'price' => $this->formattedPrice,
            'price_varies' => $this->priceVaries(),
            'stock_count' => (int) $this->stockCount(),
            'type' => $this->type->name,
            'in_stock' => $this->inStock(),
            'product' => new ProductIndexResource($this->product)
        ];
    }
}
