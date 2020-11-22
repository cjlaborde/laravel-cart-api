<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;

class ProfileJsonResponse
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        if ($response instanceof JsonResponse && app('debugbar')->isEnabled() && $request->has('_debug')) {
//            dd('works');
            // setData adds data to our response
            $response->setData($response->getData(true) + [
                    // get all data from debugbar
//                    '_debugbar' => app('debugbar')->getData()
                    // get only the queries back from debugbar
                    '_debugbar' => array_only(app('debugbar')->getData(), 'queries')
            ]);
        }

        return $response;
    }
}
