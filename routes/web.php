<?php

use App\Http\Controllers\Admin\AdminUserVerificationController;
use App\Http\Controllers\Admin\AdminVerificationController as AdminAdminVerificationController;
use App\Http\Controllers\Admin\UserVerificationController;
use App\Http\Controllers\AdminVerificationController;
use App\Http\Controllers\Auth\SocialiteController as AuthSocialiteController;
use App\Http\Controllers\SocialiteController;
use App\Http\Controllers\UserVerificationController as ControllersUserVerificationController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Route;
use Laravel\Socialite\Facades\Socialite;

Route::redirect('/', 'avs');

Route::get('/avs/verify-user/{user}', [AdminVerificationController::class, 'verifyUser'])
    ->name('admin.verify-user')
    ->middleware(['signed']);

Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    // We don't call $request->fulfill() since we don't want to mark emails as verified
    return redirect('/');
})->middleware(['signed'])->name('verification.verify');

Route::get('/verify-user/{id}/{hash}', [ControllersUserVerificationController::class, 'verify'])
    ->name('user.verify')
    ->middleware('signed');