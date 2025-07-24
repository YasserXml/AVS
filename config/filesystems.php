<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available to your application for file storage.
    |
    */

    'default' => env('FILESYSTEM_DISK', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Below you may configure as many filesystem disks as necessary, and you
    | may even configure multiple disks for the same driver. Examples for
    | most supported storage drivers are configured here for reference.
    |
    | Supported drivers: "local", "ftp", "sftp", "s3"
    |
    */

    'disks' => [
        'local' => [
            'driver' => 'local',
            'root' => storage_path('app'),
            'throw' => false,
        ],

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => env('APP_URL') . '/storage',
            'visibility' => 'public',
            'throw' => false,
        ],

        // Disk untuk setiap departemen
        'direktorat' => [
            'driver' => 'local',
            'root' => storage_path('app/public/direktorat'),
            'url' => env('APP_URL') . '/storage/direktorat',
            'visibility' => 'public',
            'throw' => false,
        ],

        'hrd_ga' => [
            'driver' => 'local',
            'root' => storage_path('app/public/divisi/hrd-ga'),
            'url' => env('APP_URL') . '/storage/divisi/hrd-ga',
            'visibility' => 'public',
            'throw' => false,
        ],

        'sekretariat' => [
            'driver' => 'local',
            'root' => storage_path('app/public/divisi/sekretariat'),
            'url' => env('APP_URL') . '/storage/divisi/sekretariat',
            'visibility' => 'public',
            'throw' => false,
        ],

        'purchasing' => [
            'driver' => 'local',
            'root' => storage_path('app/public/divisi/purchasing'),
            'url' => env('APP_URL') . '/storage/divisi/purchasing',
            'visibility' => 'public',
            'throw' => false,
        ],

        'keuangan' => [
            'driver' => 'local',
            'root' => storage_path('app/public/divisi/keuangan'),
            'url' => env('APP_URL') . '/storage/divisi/keuangan',
            'visibility' => 'public',
            'throw' => false,
        ],

        'accounting_media' => [
            'driver' => 'local',
            'root' => storage_path('app/public/accounting'),
            'url' => env('APP_URL') . '/storage/accounting',
            'visibility' => 'public',
            'throw' => false,
        ],

        'rnd' => [
            'driver' => 'local',
            'root' => storage_path('app/public/divisi/rnd'),
            'url' => env('APP_URL') . '/storage/divisi/rnd',
            'visibility' => 'public',
            'throw' => false,
        ],

        'software' => [
            'driver' => 'local',
            'root' => storage_path('app/public/divisi/game-programming'),
            'url' => env('APP_URL') . '/storage/divisi/game-programming',
            'visibility' => 'public',
            'throw' => false,
        ],

        'elektro' => [
            'driver' => 'local',
            'root' => storage_path('app/public/divisi/system-engineering'),
            'url' => env('APP_URL') . '/storage/divisi/system-engineering',
            'visibility' => 'public',
            'throw' => false,
        ],

        '3d' => [
            'driver' => 'local',
            'root' => storage_path('app/public/divisi/3d'),
            'url' => env('APP_URL') . '/storage/divisi/3d',
            'visibility' => 'public',
            'throw' => false,
        ],

        'mekanik' => [
            'driver' => 'local',
            'root' => storage_path('app/public/divisi/mekanik'),
            'url' => env('APP_URL') . '/storage/divisi/mekanik',
            'visibility' => 'public',
            'throw' => false,
        ],

        'pmo' => [
            'driver' => 'local',
            'root' => storage_path('app/public/divisi/pmo'),
            'url' => env('APP_URL') . '/storage/divisi/pmo',
            'visibility' => 'public',
            'throw' => false,
        ],

        'bisnis_marketing' => [
            'driver' => 'local',
            'root' => storage_path('app/public/divisi/bisnis-marketing'),
            'url' => env('APP_URL') . '/storage/divisi/bisnis-marketing',
            'visibility' => 'public',
            'throw' => false,
        ],

        // Disk backup untuk semua departemen
        'backup' => [
            'driver' => 'local',
            'root' => storage_path('app/backup'),
            'throw' => false,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Symbolic Links
    |--------------------------------------------------------------------------
    |
    | Here you may configure the symbolic links that will be created when the
    | `storage:link` Artisan command is executed. The array keys should be
    | the locations of the links and the values should be their targets.
    |
    */

    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],

];
