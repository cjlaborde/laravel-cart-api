<?php

namespace App\Http\Resources;

//class ProductResource extends JsonResource
class ProductResource extends ProductIndexResource
{
    /**
     * Transform the resource into an array.
     *
 * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
//        dd($this->variations->groupBy('type.name'));
        return array_merge(parent::toArray($request), [
            'variations' => ProductVariationResource::collection(
                $this->variations->groupBy('type.name')
            )
        ]);
    }
}
