<?php

namespace App\Http\Middleware;

use App\Exceptions\ConsentRequestException;
use Closure;
use Illuminate\Http\Request;

class ConsentChallenge
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->missing('consent_challenge')) {
            throw new ConsentRequestException("Cannot find the 'consent_challenge' from request");
        }

        return $next($request);
    }
}
