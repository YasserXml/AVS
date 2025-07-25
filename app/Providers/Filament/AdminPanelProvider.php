<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Auth\EmailVerification;
use App\Filament\Pages\Auth\Login;
use App\Filament\Pages\Auth\Register;
use App\Filament\Pages\Auth\ResetPassword;
use App\Filament\Widgets\BarangChartWidget;
use App\Filament\Widgets\BarangKeluarChartWidget;
use App\Filament\Widgets\BarangKeluarWidget;
use App\Filament\Widgets\BarangMasukChartWidget;
use App\Filament\Widgets\BarangMasukWidget;
use App\Filament\Widgets\BarangWidget;
use App\Filament\Widgets\PengajuanOprasionalWidget;
use App\Filament\Widgets\StatsOverviewWidget;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use CodeWithDennis\FilamentThemeInspector\FilamentThemeInspector;
use CodeWithDennis\FilamentThemeInspector\FilamentThemeInspectorPlugin;
use Devonab\FilamentEasyFooter\EasyFooterPlugin;
use Filafly\Icons\FontAwesome\FontAwesomeIcons;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Hasnayeen\Themes\Http\Middleware\SetTheme;
use Hasnayeen\Themes\ThemesPlugin;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
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
            ->sidebarWidth('15rem')
            // ->topNavigation()
            ->login(Login::class)
            ->registration(Register::class)
            ->breadcrumbs(false)
            ->spa()
            ->profile(isSimple: false)
            ->loginRouteSlug('login')
            ->registrationRouteSlug('registrasi')
            ->passwordResetRouteSlug('reset-password')
            ->emailVerificationRouteSlug('verifikasi-email')
            ->databaseNotifications()
            ->databaseNotificationsPolling('10s')
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
                StatsOverviewWidget::class,
                BarangMasukChartWidget::class,
                BarangKeluarChartWidget::class,
                PengajuanOprasionalWidget::class,
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
                EasyFooterPlugin::make()
                    ->withFooterPosition('footer')
                    ->withLoadTime()
                    ->withBorder()  
                    ->withLogo(
                        asset('images/Logo.png'),
                        'https://avsimulator.com/',
                    )
                    ->hiddenFromPagesEnabled()
                    ->hiddenFromPages(['admin/login', 'admin/registrasi']),
                FontAwesomeIcons::make()
                    ->classicRegular()
                    ->free(),
            ])
            ->authMiddleware([
                Authenticate::class,
                'auth',
            ]);
    }
}
