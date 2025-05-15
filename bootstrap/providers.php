<?php

use CodeWithDennis\FilamentThemeInspector\FilamentThemeInspectorServiceProvider;
use Laravel\Sanctum\SanctumServiceProvider;

return [
    App\Providers\AppServiceProvider::class,
    App\Providers\Filament\AdminPanelProvider::class,
    Spatie\Permission\PermissionServiceProvider::class,
    BezhanSalleh\FilamentShield\FilamentShieldServiceProvider::class,
    // Laravel\Socialite\SocialiteServiceProvider::class,
    // SanctumServiceProvider::class,
    // FilamentThemeInspectorServiceProvider::class,
];
