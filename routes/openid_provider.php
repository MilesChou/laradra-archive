<?php

use Illuminate\Routing\Router;

/** @var Router $router */

$router->get('/provider/login', 'Login\Provider')->name('openid_provider.login.provider');
$router->post('/provider/login', 'Login\Accept')->name('openid_provider.login.accept');
$router->post('/provider/login/reject', 'Login\Reject')->name('openid_provider.login.reject');
$router->get('/provider/consent', 'Consent\Provider')->name('openid_provider.consent.provider');
$router->post('/provider/consent', 'Consent\Accept')->name('openid_provider.consent.accept');
$router->post('/provider/consent/reject', 'Consent\Reject')->name('openid_provider.consent.reject');
$router->get('/provider/logout', 'Logout\Provider')->name('openid_provider.logout.provider');
$router->post('/provider/logout', 'Logout\Accept')->name('openid_provider.logout.accept');
$router->post('/provider/logout/reject', 'Logout\Reject')->name('openid_provider.logout.reject');
