<?php

return [
    'backup' => [
        'name' => env('APP_NAME', 'laravel-backup'),

        'source' => [
            'files' => [
                'include' => [
                    base_path(),
                ],
                'exclude' => [
                    base_path('vendor'),
                    base_path('node_modules'),
                    base_path('.git'),
                    base_path('storage/app/backup'), // exclude backup folder
                    base_path('storage/app/backup-temp'), // exclude temp folder
                    
                    // Optional: exclude folder departemen yang besar
                    // base_path('storage/app/public/divisi/3d'),
                    // base_path('storage/app/public/accounting'),
                ],
                'follow_links' => false,
                'ignore_unreadable_directories' => false,
                'relative_path' => null,
            ],

            // PostgreSQL Database Configuration
            'databases' => [
                env('DB_CONNECTION', 'pgsql'), // Ganti dari mysql ke pgsql
            ],
        ],

        // Aktifkan kompresi untuk database PostgreSQL
        'database_dump_compressor' => \Spatie\DbDumper\Compressors\GzipCompressor::class,
        
        'database_dump_file_timestamp_format' => 'Y-m-d-H-i-s',
        'database_dump_filename_base' => 'database',
        'database_dump_file_extension' => '',

        'destination' => [
            'compression_method' => ZipArchive::CM_DEFAULT,
            'compression_level' => 9, 
            'filename_prefix' => '',
            'disks' => [
                'backup', 
            ],
        ],

        'temporary_directory' => storage_path('app/backup-temp'),
        'password' => env('BACKUP_ARCHIVE_PASSWORD'),
        'encryption' => 'default',
        'tries' => 3, 
        'retry_delay' => 5, 
    ],

    'notifications' => [
        'notifications' => [
            \Spatie\Backup\Notifications\Notifications\BackupHasFailedNotification::class => ['mail'],
            \Spatie\Backup\Notifications\Notifications\UnhealthyBackupWasFoundNotification::class => ['mail'],
            \Spatie\Backup\Notifications\Notifications\CleanupHasFailedNotification::class => ['mail'],
            \Spatie\Backup\Notifications\Notifications\BackupWasSuccessfulNotification::class => ['mail'],
            \Spatie\Backup\Notifications\Notifications\HealthyBackupWasFoundNotification::class => ['mail'],
            \Spatie\Backup\Notifications\Notifications\CleanupWasSuccessfulNotification::class => ['mail'],
        ],

        'notifiable' => \Spatie\Backup\Notifications\Notifiable::class,

        'mail' => [
            'to' => env('BACKUP_NOTIFICATION_EMAIL', 'admin@yourcompany.com'),
            'from' => [
                'address' => env('MAIL_FROM_ADDRESS', 'backup@yourcompany.com'),
                'name' => env('MAIL_FROM_NAME', 'Laravel Backup'),
            ],
        ],
    ],

    // Update monitoring untuk backup bulanan
    'monitor_backups' => [
        [
            'name' => env('APP_NAME', 'laravel-backup'),
            'disks' => ['backup'], // Ganti dari 'local' ke 'backup'
            'health_checks' => [
                \Spatie\Backup\Tasks\Monitor\HealthChecks\MaximumAgeInDays::class => 35, // 35 hari untuk backup bulanan
                \Spatie\Backup\Tasks\Monitor\HealthChecks\MaximumStorageInMegabytes::class => 15000, // 15GB untuk banyak departemen
            ],
        ],
    ],

    'cleanup' => [
        'strategy' => \Spatie\Backup\Tasks\Cleanup\Strategies\DefaultStrategy::class,
        
        // Optimasi untuk backup bulanan
        'default_strategy' => [
            'keep_all_backups_for_days' => 35,        // Simpan 35 hari (lebih dari 1 bulan)
            'keep_daily_backups_for_days' => 0,       // Skip daily (karena backup bulanan)
            'keep_weekly_backups_for_weeks' => 0,     // Skip weekly
            'keep_monthly_backups_for_months' => 12,  // Simpan 12 backup bulanan
            'keep_yearly_backups_for_years' => 3,     // Simpan 3 backup tahunan
            'delete_oldest_backups_when_using_more_megabytes_than' => 15000, // 15GB limit
        ],
        
        'tries' => 3,
        'retry_delay' => 5,
    ],
];