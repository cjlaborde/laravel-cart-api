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
        // Goes through each of the scope and apply the scope here with what is in the request
        // key is what we looking for in the request
       foreach ($this->limitScopes($scopes) as $key => $scope) {
           // if the scope doesn't match with the interface ignore it
           if (!$scope instanceof Scope) {
               continue;
           }


           // will apply score before anything else we pass in the url and return the builder
            $scope->apply($builder, $this->request->get($key));
       }

       return $builder;
    }

    // return a limited collection of scopes
    // Base on what is on the query string
    protected function limitScopes(array $scopes)
    {
        // only pluck out the items keys from what we requested
        // should only give us back only items in scopes // 'category' => class
        return array_only(
            $scopes,
            array_keys($this->request->all())
        );
    }
}
