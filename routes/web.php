<?php

use App\Http\Controllers\Rp\Callback;
use App\Http\Controllers\Rp\Login;
use App\Http\Controllers\Rp\RefreshToken;
use Illuminate\Support\Facades\Route;

Route::get('/rp/login', Login::class);
Route::get('/rp/callback', Callback::class);
Route::get('/rp/refresh', RefreshToken::class);
