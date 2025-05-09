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

Route::redirect('/', 'admin');

Route::get('/admin/login/{provider}/redirect', [SocialiteController::class, 'redirect'])
    ->name('filament.admin.auth.socialite.redirect');

Route::get('/admin/login/{provider}/callback', [SocialiteController::class, 'handleCallback'])
    ->name('filament.admin.auth.socialite.callback');

Route::get('/admin/verify-user/{user}', [AdminVerificationController::class, 'verifyUser'])
    ->name('admin.verify-user')
    ->middleware(['signed']);

Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    // We don't call $request->fulfill() since we don't want to mark emails as verified
    return redirect('/');
})->middleware(['signed'])->name('verification.verify');

Route::get('/verify-user/{id}/{hash}', [ControllersUserVerificationController::class, 'verify'])
    ->name('user.verify')
    ->middleware('signed');

// Route::get('/email/verify', function () {
//     return view('auth.verify-email');
// })->middleware('auth')->name('verification.notice');

// Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
//     $request->fulfill();
//     return redirect('/')->with('verified', true);
// })->middleware(['auth', 'signed'])->name('verification.verify');

// Route::post('/email/verification-notification', function (Request $request) {
//     $request->user()->sendEmailVerificationNotification();
//     return back()->with('message', 'Link verifikasi telah dikirim!');
// })->middleware(['auth', 'throttle:6,1'])->name('verification.send');

// Route::prefix('admin')->middleware(['web'])->group(function () {
//     Route::get('verify-user/{userId}', [AdminUserVerificationController::class, 'verifyUser'])
//         ->name('admin.verify-user');

//     Route::get('reject-user/{userId}', [AdminUserVerificationController::class, 'rejectUser'])
//         ->name('admin.reject-user');
// });

// Route::middleware(['signed'])->group(function () {
//     Route::get('/admin/verify-user/{userId}', [AdminVerificationController::class, 'verifyUser'])
//         ->name('admin.verify-user');
    
//     Route::get('/admin/reject-user/{userId}', [AdminVerificationController::class, 'rejectUser'])
//         ->name('admin.reject-user');
// });

// Route::get('/admin/verify-user/{user_id}', [UserVerificationController::class, 'verifyUser'])
//     ->middleware(['signed', 'role:super_admin|admin'])
//     ->name('admin.verify-user');