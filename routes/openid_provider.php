<?php

use App\Http\Controllers\Provider\Consent\Accept as ConsentAccept;
use App\Http\Controllers\Provider\Consent\Provider as ConsentProvider;
use App\Http\Controllers\Provider\Consent\Reject as ConsentReject;
use App\Http\Controllers\Provider\Login\Accept as LoginAccept;
use App\Http\Controllers\Provider\Login\Provider as LoginProvider;
use App\Http\Controllers\Provider\Login\Reject as LoginReject;
use App\Http\Controllers\Provider\Logout\Accept as LogoutAccept;
use App\Http\Controllers\Provider\Logout\Provider as LogoutProvider;
use App\Http\Controllers\Provider\Logout\Reject as LogoutReject;
use Illuminate\Support\Facades\Route;

Route::get('/provider/login', LoginProvider::class)
    ->name('openid_provider.login.provider');
Route::post('/provider/login', LoginAccept::class)
    ->name('openid_provider.login.accept');
Route::post('/provider/login/reject', LoginReject::class)
    ->name('openid_provider.login.reject');
Route::get('/provider/consent', ConsentProvider::class)
    ->name('openid_provider.consent.provider');
Route::post('/provider/consent', ConsentAccept::class)
    ->name('openid_provider.consent.accept');
Route::post('/provider/consent/reject', ConsentReject::class)
    ->name('openid_provider.consent.reject');
Route::get('/provider/logout', LogoutProvider::class)
    ->name('openid_provider.logout.provider');
Route::post('/provider/logout', LogoutAccept::class)
    ->name('openid_provider.logout.accept');
Route::post('/provider/logout/reject', LogoutReject::class)
    ->name('openid_provider.logout.reject');
