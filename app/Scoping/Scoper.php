<?php

namespace App\Scoping;

use App\Scoping\Contracts\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class Scoper
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function apply(Builder $builder, array $scopes)
    {
        // key is what we looking for in the request
       foreach ($scopes as $key => $scope) {
           // if the scope doesn't match with the interface ignore it
           if (!$scope instanceof Scope) {
               continue;
           }


           // will apply score before anything else we pass in the url and return the builder
            $scope->apply($builder, $this->request->get($key));
       }

       return $builder;
    }
}
