<?php

return [

    'label' => 'Profil',

    'form' => [

        'email' => [
            'label' => 'Alamat email',
        ],

        'name' => [
            'label' => 'Nama',
        ],

        'password' => [
            'label' => 'Kata sandi baru',
        ],

        'password_confirmation' => [
            'label' => 'Konfirmasi kata sandi baru',
        ],

        'actions' => [

            'save' => [
                'label' => 'Simpan',
            ],

        ],

    ],

    'notifications' => [

        'saved' => [
            'title' => 'Disimpan',
        ],

    ],

    'actions' => [

        'cancel' => [
            'label' => 'Kembali',
        ],

    ],

    'messages' => [

        'failed' => 'Email atau kata sandi tidak valid , mohon cek kembali.',
        'throttled' => 'Terlalu banyak upaya masuk. Silakan coba lagi dalam beberapa saat',

    ],

];
