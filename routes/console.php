<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('backup:run')
        ->monthly()
        ->at('02:00')
        ->onFailure(function () {
            Log::error('Monthly backup failed at ' . now(), [
                'timestamp' => now(),
                'disk_space' => disk_free_space(storage_path('app/backup'))
            ]);
        })
        ->onSuccess(function () {
            Log::info('Monthly backup completed successfully at ' . now(), [
                'timestamp' => now(),
                'backup_location' => storage_path('app/backup')
            ]);
        })
        ->appendOutputTo(storage_path('logs/backup.log'));

// Cleanup backup setiap 3 bulan (quarterly) - jam 03:00
Schedule::command('backup:clean')
        ->quarterly()
        ->at('03:00')
        ->onSuccess(function () {
            Log::info('Backup cleanup completed at ' . now());
        })
        ->appendOutputTo(storage_path('logs/backup-cleanup.log'));

// Monitor backup setiap minggu (Sabtu jam 08:00)
Schedule::command('backup:monitor')
        ->weekly()
        ->saturdays()
        ->at('08:00')
        ->onFailure(function () {
            Log::warning('Backup monitor found issues at ' . now());
        })
        ->appendOutputTo(storage_path('logs/backup-monitor.log'));
