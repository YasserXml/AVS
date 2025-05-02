<?php

return [

    'title' => 'Masuk',

    'heading' => 'Masuk ke akun Anda',

    'actions' => [

        'register' => [
            'before' => 'atau',
            'label' => 'buat akun baru',
        ],

        'request_password_reset' => [
            'label' => 'Lupa kata sandi?',
        ],

    ],

    'form' => [

        'email' => [
            'label' => 'Alamat email',
        ],

        'password' => [
            'label' => 'Kata sandi',
        ],

        'remember' => [
            'label' => 'Ingat saya',
        ],

        'actions' => [

            'authenticate' => [
                'label' => 'Masuk',
            ],

        ],

    ],

    'messages' => [

        'failed' => 'Email atau kata sandi tidak valid , mohon cek kembali.',
        'throttled' => 'Terlalu banyak upaya masuk. Silakan coba lagi dalam beberapa saat',

    ],

    'notifications' => [

        'throttled' => [
            'title' => 'Terlalu banyak permintaan',
            'body' => 'Silakan coba lagi dalam beberapa saat',
        ],

    ],

];
