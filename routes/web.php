<?php

use App\Http\Controllers\Auth\SocialiteController as AuthSocialiteController;
use App\Http\Controllers\SocialiteController;
use Illuminate\Support\Facades\Route;
use Laravel\Socialite\Facades\Socialite;

Route::redirect('/', 'admin');

Route::get('/admin/login/{provider}', function ($provider) {
    return Socialite::driver($provider)->redirect();
})->name('socialite.redirect');

Route::get('/admin/login/{provider}/callback', [App\Http\Controllers\Auth\SocialiteController::class, 'handleCallback'])
    ->name('socialite.callback');