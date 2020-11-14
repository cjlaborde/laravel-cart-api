<?php

namespace App\Scoping\Scopes;

use App\Scoping\Contracts\Scope;
use Illuminate\Database\Eloquent\Builder;

class CategoryScope implements Scope
{
    public function apply(Builder $builder, $value)
    {
        // value we get in the request
//        dd($value);
//        return $builder->where('slug', $value);
        return $builder->whereHas('categories', function ($builder) use ($value) {
            // where the slug = the value
            $builder->where('slug', $value);
        });
    }
}
