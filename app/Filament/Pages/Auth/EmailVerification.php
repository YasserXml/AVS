<?php

namespace App\Filament\Pages\Auth;

use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use DanHarrin\LivewireRateLimiting\WithRateLimiting;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Http\Responses\Auth\Contracts\EmailVerificationResponse;
use Filament\Pages\Auth\EmailVerification\EmailVerificationPrompt as BaseEmailVerificationPrompt;


use Illuminate\Support\Facades\Auth;

class EmailVerification extends BaseEmailVerificationPrompt
{
    use WithRateLimiting;

    public function resendNotification(): void
    {
        try {
            $this->rateLimit(2);
        } catch (TooManyRequestsException $exception) {
            Notification::make()
                ->title('Batas percobaan tercapai')
                ->body('Terlalu banyak permintaan. Silakan coba lagi dalam' . ceil($exception->secondsUntilAvailable / 60) . 'menit.')
                ->danger()
                ->send();

            return;
        }

        $user = Auth::user();

        if (! $user) {
            return;
        }

        // Send the verification email directly
        $user->sendEmailVerificationNotification();

        Notification::make()
            ->title('Email verifikasi telah dikirim')
            ->body('Email verifikasi telah dikirim ke alamat email Anda.')
            ->success()
            ->send();
    }

    public function verify(): ?EmailVerificationResponse
    {
        $user = Auth::user();

        if (! $user) {
            return null;
        }

        if ($user->hasVerifiedEmail()) {
            return app(EmailVerificationResponse::class);
        }

        $user->markEmailAsVerified();
        
        Notification::make()
            ->title('Email berhasil diverifikasi')
            ->body('Email Anda berhasil diverifikasi. Sekarang tunggu verifikasi dari admin.')
            ->success()
            ->send();

        return app(EmailVerificationResponse::class);
    }
}