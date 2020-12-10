<?php

namespace App\Http\Controllers\Provider\Logout;

use Illuminate\Http\Request;
use Ory\Hydra\Client\Api\AdminApi;

class Provider
{
    public function __invoke(Request $request, AdminApi $hydra)
    {
        $logoutChallenge = $request->get('logout_challenge');

        return view('provider.logout_page', [
            'logout_challenge' => $logoutChallenge,
        ]);
    }
}
