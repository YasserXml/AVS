<?php

namespace App\Providers;

use App\Models\Pengajuanoprasional;
use App\Models\Pengembalian;
use App\Models\User;
use App\Observers\PengajuanoprasionalObserver;
use App\Observers\PengembalianObserver;
use App\Observers\UserObserver;
use Filament\Facades\Filament;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
       User::observe(UserObserver::class);
    }
}

