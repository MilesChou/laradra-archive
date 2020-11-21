<?php

namespace App\Http\Controllers\Provider;

use Illuminate\Http\Request;
use Ory\Hydra\Client\Api\AdminApi;
use Ory\Hydra\Client\Model\AcceptLoginRequest;

class LoginAction
{
    public function __invoke(Request $request, AdminApi $admin)
    {
        if ('foobar' !== $request->get('password')) {
            return redirect()->back();
        }

        $loginChallenge = $request->get('login_challenge');

        $completed = $admin->acceptLoginRequest($loginChallenge, new AcceptLoginRequest([
            'subject' => $request->get('email'),
            'remember' => true,
            'remember_for' => 3600,
        ]));

        return redirect()->to($completed->getRedirectTo());
    }
}
