<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {

            if (auth()->user()->tokenCan('server:admin')) {
                return $next($request);
            } else {
                return response()->json([
                    'status' => 403,
                    'message' => 'Samo Admin ima pristup ovoj stranici!'
                ], 403);
            }
        } else {
            return response()->json([
                'status' => 404,
                'message' => 'Morate biti ulogovani da biste pristupili ovoj stranici!'
            ]);
        }
    }
}
