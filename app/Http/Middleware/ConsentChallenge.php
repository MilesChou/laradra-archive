<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use RuntimeException;

class ConsentChallenge
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->missing('consent_challenge')) {
            throw new RuntimeException("Cannot find the 'consent_challenge' from request");
        }

        return $next($request);
    }
}
