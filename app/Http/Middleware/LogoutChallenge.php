<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use RuntimeException;

class LogoutChallenge
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->missing('logout_challenge')) {
            throw new RuntimeException("Cannot find the 'logout_challenge' from request");
        }

        return $next($request);
    }
}
