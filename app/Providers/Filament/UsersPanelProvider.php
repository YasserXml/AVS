<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class UsersPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('users')
            ->path('users')
            ->login()
            ->profile(isSimple: false)
            ->authGuard('web')
            // ->topNavigation()
            ->login()
            ->registration()
            ->passwordReset()
            ->sidebarCollapsibleOnDesktop()
            ->emailVerification()
            ->profile(isSimple: false)
            ->loginRouteSlug('login')
            ->registrationRouteSlug('registrasi')
            ->passwordResetRouteSlug('reset-password')
            ->emailVerificationRouteSlug('verifikasi-email')
            ->databaseNotifications()
            ->databaseNotificationsPolling('30s')
            ->brandName('AVSimulator')
            ->brandLogo(fn() => view('logo-change.logo'))
            ->favicon(asset('images/Logo(1).webp'))
            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(in: app_path('Filament/Users/Resources'), for: 'App\\Filament\\Users\\Resources')
            ->discoverPages(in: app_path('Filament/Users/Pages'), for: 'App\\Filament\\Users\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Users/Widgets'), for: 'App\\Filament\\Users\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
                // Widgets\FilamentInfoWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
