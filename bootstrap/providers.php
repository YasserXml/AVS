<?php

return [
    App\Providers\AppServiceProvider::class,
    App\Providers\Filament\AdminPanelProvider::class,
    // App\Providers\Filament\UsersPanelProvider::class,
    BezhanSalleh\FilamentShield\FilamentShieldServiceProvider::class,
    Spatie\Permission\PermissionServiceProvider::class,
];
