<?php

use Illuminate\Routing\Router;

/** @var Router $router */

$router->get('/rp/login', 'RpController@login');
$router->get('/rp/callback', 'RpController@callback');
