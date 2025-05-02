<?php

use App\Http\Controllers\Auth\SocialiteController as AuthSocialiteController;
use App\Http\Controllers\SocialiteController;
use Illuminate\Support\Facades\Route;
use Laravel\Socialite\Facades\Socialite;

Route::redirect('/', 'admin');

Route::get('/auth/redirect/{provider}', function ($provider) {
    return Socialite::driver($provider)->redirect();
})->name('socialite.redirect');

Route::get('/auth/callback/{provider}', [AuthSocialiteController::class, 'handleCallback'])
    ->name('socialite.callback');

Route::get('/auth/callback/google', 'App\Http\Controllers\Auth\LoginController@handleGoogleCallback')->name('google.callback');
