<?php

namespace App\Models\Traits;

use App\Scoping\Scoper;
use Illuminate\Database\Eloquent\Builder;

trait CanBeScoped
{
    # within every scope we get a Builder
    # we take that builder and take what we pass in and filter it further down
    # pass it the scopes we going to be using
    public function scopeWithScopes(Builder $builder, $scopes = [])
    {
        # we need to pass through the builder and pass in the scopes we want to use.
        return (new Scoper(request()))->apply($builder, $scopes);
    }
}
