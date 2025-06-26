<?php

namespace App\Filament\Pages\Auth;

use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use DanHarrin\LivewireRateLimiting\WithRateLimiting;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Concerns\InteractsWithFormActions;
use Filament\Pages\SimplePage;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Password;

class RequestPassword extends SimplePage
{
    use InteractsWithFormActions;
    use WithRateLimiting;

    /**
     * @var view-string
     */
    protected static string $view = 'filament.pages.auth.custom-request-password-reset';

    /**
     * @var array<string, mixed> | null
     */
    public ?array $data = [];

    public function mount(): void
    {
        if (Filament::auth()->check()) {
            redirect()->intended(Filament::getUrl());
        }

        $this->form->fill();
    }

    public function request(): void
    {
        try {
            $this->rateLimit(2);
        } catch (TooManyRequestsException $exception) {
            $this->getRateLimitedNotification($exception)?->send();
            return;
        }

        $data = $this->form->getState();

        $status = Password::broker(Filament::getAuthPasswordBroker())->sendResetLink(
            $data,
            function (CanResetPassword $user, string $token): void {
                if (! method_exists($user, 'notify')) {
                    $userClass = $user::class;
                    throw new \Exception("Model [{$userClass}] tidak memiliki method [notify()].");
                }

                $notification = app(ResetPassword::class, ['token' => $token]);
                $notification->url = Filament::getResetPasswordUrl($token, $user);

                $user->notify($notification);
            },
        );

        if ($status !== Password::RESET_LINK_SENT) {
            Notification::make()
                ->title($this->getStatusMessage($status))
                ->danger()
                ->send();
            return;
        }

        Notification::make()
            ->title('Link reset password telah dikirim ke email Anda!')
            ->body('Silakan periksa email Anda dan ikuti instruksi untuk mereset password.')
            ->success()
            ->send();

        $this->form->fill();
    }

    protected function getRateLimitedNotification(TooManyRequestsException $exception): ?Notification
    {
        return Notification::make()
            ->title('Terlalu banyak percobaan!')
            ->body("Silakan tunggu {$exception->secondsUntilAvailable} detik sebelum mencoba lagi.")
            ->danger();
    }

    protected function getStatusMessage(string $status): string
    {
        return match ($status) {
            Password::INVALID_USER => 'Email tidak ditemukan dalam sistem kami.',
            Password::INVALID_TOKEN => 'Token reset password tidak valid.',
            Password::RESET_THROTTLED => 'Terlalu banyak percobaan reset. Silakan coba lagi nanti.',
            default => 'Terjadi kesalahan. Silakan coba lagi.',
        };
    }

    public function form(Form $form): Form
    {
        return $form;
    }

    /**
     * @return array<int | string, string | Form>
     */
    protected function getForms(): array
    {
        return [
            'form' => $this->form(
                $this->makeForm()
                    ->schema([
                        $this->getEmailFormComponent(),
                    ])
                    ->statePath('data'),
            ),
        ];
    }

    protected function getEmailFormComponent(): Component
    {
        return TextInput::make('email')
            ->label('Alamat Email')
            ->placeholder('Masukkan alamat email Anda')
            ->email()
            ->required()
            ->autocomplete('email')
            ->autofocus()
            ->validationMessages([
                'required' => 'Email wajib diisi.',
                'email' => 'Format email tidak valid.',
            ]);
    }

    public function loginAction(): Action
    {
        return Action::make('login')
            ->link()
            ->label('Kembali ke Login')
            ->icon('heroicon-m-arrow-left')
            ->url(filament()->getLoginUrl());
    }

    public function getTitle(): string | Htmlable
    {
        return 'Reset Password';
    }

    public function getHeading(): string | Htmlable
    {
        return 'Lupa Password?';
    }

    public function getSubheading(): string | Htmlable
    {
        return 'Masukkan alamat email Anda dan kami akan mengirimkan link untuk mereset password.';
    }

    /**
     * @return array<Action | ActionGroup>
     */
    protected function getFormActions(): array
    {
        return [
            $this->getRequestFormAction(),
        ];
    }

    protected function getRequestFormAction(): Action
    {
        return Action::make('request')
            ->label('Kirim Link Reset')
            ->color('primary')
            ->size('lg')
            ->submit('request');
    }

    protected function hasFullWidthFormActions(): bool
    {
        return true;
    }

    /**
     * @return array<Action | ActionGroup>
     */
    protected function getFooterActions(): array
    {
        return [
            $this->loginAction(),
        ];
    }

    protected function hasFullWidthFooterActions(): bool
    {
        return true;
    }
}