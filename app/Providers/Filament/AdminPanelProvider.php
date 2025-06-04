<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Auth\EmailVerification;
use App\Filament\Pages\Auth\Login;
use App\Filament\Pages\Auth\Register;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use CodeWithDennis\FilamentThemeInspector\FilamentThemeInspector;
use CodeWithDennis\FilamentThemeInspector\FilamentThemeInspectorPlugin;
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
use Jeffgreco13\FilamentBreezy\BreezyCore;
use TomatoPHP\FilamentMediaManager\FilamentMediaManagerPlugin;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->authGuard('web')
            ->topNavigation()
            ->login(Login::class)
            ->registration(Register::class)
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
                'primary' => Color::Red,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->discoverClusters(in: app_path('Filament/Clusters'), for: 'App\\Filament\\Clusters')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
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
            ->plugins([
                FilamentShieldPlugin::make(),
                // FilamentThemeInspectorPlugin::make()
                //     ->disabled(fn()=> ! app()->hasDebugModeEnabled()),
                FilamentMediaManagerPlugin::make()
                ->allowSubFolders(),
                
            ])
            ->authMiddleware([
                Authenticate::class,
                'auth',
            ]);
    }
}
