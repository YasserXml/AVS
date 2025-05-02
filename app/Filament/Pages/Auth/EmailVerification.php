<?php

namespace App\Filament\Pages\Auth;

use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use DanHarrin\LivewireRateLimiting\WithRateLimiting;
use Filament\Facades\Filament;
use Filament\Http\Responses\Auth\Contracts\EmailVerificationResponse;
use Filament\Notifications\Auth\VerifyEmail;
use Filament\Notifications\Notification;
use Filament\Pages\Auth\EmailVerification as BaseEmailVerification;
use Illuminate\Support\Facades\Auth;

// class EmailVerification extends BaseEmailVerification
// {
//     use WithRateLimiting;

//     public function resendNotification(): void
//     {
//         try {
//             $this->rateLimit(2);
//         } catch (TooManyRequestsException $exception) {
//             Notification::make()
//                 ->title(__('filament-panels::pages.auth.email-verification.notifications.throttled.title', [
//                     'seconds' => $exception->secondsUntilAvailable,
//                     'minutes' => ceil($exception->secondsUntilAvailable / 60),
//                 ]))
//                 ->body(__('filament-panels::pages.auth.email-verification.notifications.throttled.body', [
//                     'seconds' => $exception->secondsUntilAvailable, 
//                     'minutes' => ceil($exception->secondsUntilAvailable / 60),
//                 ]))
//                 ->danger()
//                 ->send();

//             return;
//         }

//         $user = Auth::user();

//         if (!$user) {
//             return;
//         }

//         VerifyEmail::send($user);

//         Notification::make()
//             ->title(__('filament-panels::pages.auth.email-verification.notifications.notification_resent.title'))
//             ->success()
//             ->send();
//     }

//     public function verify(): ?EmailVerificationResponse
//     {
//         $user = Auth::user();

//         if (!$user) {
//             return null;
//         }

//         if ($user->hasVerifiedEmail()) {
//             return app(EmailVerificationResponse::class);
//         }

//         $user->markEmailAsVerified();

//         return app(EmailVerificationResponse::class);
//     }
// }