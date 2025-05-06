<?php

use App\Http\Controllers\AdminVerificationController;
use App\Http\Controllers\Auth\SocialiteController as AuthSocialiteController;
use App\Http\Controllers\SocialiteController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Route;
use Laravel\Socialite\Facades\Socialite;

Route::redirect('/', 'admin');

Route::get('/auth/{provider}/redirect', function ($provider) {
    return Socialite::driver($provider)->redirect();
})->name('auth.socialite.redirect');

Route::get('/auth/{provider}/callback', [AuthSocialiteController::class, 'handleCallback'])
    ->name('auth.socialite.callback');

Route::get('/email/verify', function () {
    return view('auth.verify-email');
})->middleware('auth')->name('verification.notice');

Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();
    return redirect('/')->with('verified', true);
})->middleware(['auth', 'signed'])->name('verification.verify');

Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();
    return back()->with('message', 'Link verifikasi telah dikirim!');
})->middleware(['auth', 'throttle:6,1'])->name('verification.send');

Route::get('/admin/verify-user/{id}/{hash}/{token}', [AdminVerificationController::class, 'verify'])
    ->middleware(['signed'])
    ->name('admin.verify-user');

