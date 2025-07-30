<?php

use App\Filament\Resources\DirektoratmediaResource;
use App\Filament\Resources\DirektoratmediaResource\Pages\ListDirektoratmedia;
use App\Filament\Resources\PengajuanoprasionalResource;
use App\Http\Controllers\Admin\AdminUserVerificationController;
use App\Http\Controllers\Admin\AdminVerificationController as AdminAdminVerificationController;
use App\Http\Controllers\Admin\UserVerificationController;
use App\Http\Controllers\AdminVerificationController;
use App\Http\Controllers\Auth\SocialiteController as AuthSocialiteController;
use App\Http\Controllers\DownloadController;
use App\Http\Controllers\DownloadProjectController;
use App\Http\Controllers\SocialiteController;
use App\Http\Controllers\UserVerificationController as ControllersUserVerificationController;
use App\Models\Direktoratfolder;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Route;
use Laravel\Socialite\Facades\Socialite;

Route::redirect('/', '/avs');

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

Route::middleware(['auth'])->group(function () {
    Route::post('/download-file', [DownloadController::class, 'downloadFile'])->name('download.file');
    Route::post('/download-multiple', [DownloadController::class, 'downloadMultipleFiles'])->name('download.multiple');
    Route::get('/preview-file', [DownloadController::class, 'previewFile'])->name('preview.file');
});


Route::middleware(['auth'])->group(function () {
    // Route untuk download file tunggal project
    Route::post('/download/project/file', [DownloadProjectController::class, 'downloadFile'])->name('download.project.file');

    // Route untuk download multiple files sebagai ZIP
    Route::post('/download/project/multiple', [DownloadProjectController::class, 'downloadMultipleFiles'])->name('download.project.multiple');

    // Route untuk download semua file project
    Route::post('/download/project/all', [DownloadProjectController::class, 'downloadAllProjectFiles'])->name('download.project.all');

    // Route untuk download file barang spesifik
    Route::post('/download/project/barang', [DownloadProjectController::class, 'downloadBarangFiles'])->name('download.project.barang');

    // Route untuk preview file - HANYA GUNAKAN GET
    Route::get('/preview/project/file', [DownloadProjectController::class, 'previewFile'])->name('preview.project.file');
});
