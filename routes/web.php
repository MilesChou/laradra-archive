<?php

use Illuminate\Routing\Router;

/** @var Router $router */

$router->fallback(function () {
    return '404';
});
