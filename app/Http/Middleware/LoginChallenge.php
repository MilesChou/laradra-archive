<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use RuntimeException;

class LoginChallenge
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->missing('login_challenge')) {
            throw new RuntimeException("Cannot find the 'login_challenge' from request");
        }

        return $next($request);
    }
}
