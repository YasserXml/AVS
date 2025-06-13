<?php

use App\Http\Controllers\Admin\AdminUserVerificationController;
use App\Http\Controllers\Admin\AdminVerificationController as AdminAdminVerificationController;
use App\Http\Controllers\Admin\UserVerificationController;
use App\Http\Controllers\AdminVerificationController;
use App\Http\Controllers\Auth\SocialiteController as AuthSocialiteController;
use App\Http\Controllers\SocialiteController;
use App\Http\Controllers\UserVerificationController as ControllersUserVerificationController;
use App\Models\Direktoratfolder;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Route;
use Laravel\Socialite\Facades\Socialite;

Route::redirect('/', 'admin');

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

// Route::bind('folder', function ($slug) {
//     return Direktoratfolder::where('slug', $slug)->firstOrFail();
// });

// // Route untuk akses folder dengan slug
// Route::bind('folder', function ($slug) {
//     return Direktoratfolder::where('slug', $slug)->firstOrFail();
// });

// // Route untuk akses langsung folder dengan slug (redirect ke resource)
// Route::get('/admin/arsip/direktorat/folder/{folder}', function (Direktoratfolder $folder) {
//     return redirect()->route('filament.admin.resources.arsip.direktorat.folder.index', [
//         'folder' => $folder->slug
//     ]);
// })->name('arsip.direktorat.folder.show');

// // Route untuk folder yang terproteksi password
// Route::post('/admin/arsip/direktorat/folder/{folder}/verify-password', function (Direktoratfolder $folder) {
//     $password = request()->input('password');

//     if ($folder->password === $password) {
//         session()->put('folder_password_' . $folder->id, true);
//         return redirect()->route('filament.admin.resources.arsip.direktoratS.folder.index', [
//             'folder' => $folder->slug
//         ])->with('success', 'Password benar. Anda dapat mengakses folder ini.');
//     }

//     return redirect()->back()->with('error', 'Password salah.');
// })->name('arsip.direktorat.folder.verify-password');
