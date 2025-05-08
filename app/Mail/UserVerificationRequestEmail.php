<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;

// class UserVerificationRequestEmail extends Mailable
// {
//     use Queueable, SerializesModels;

//     /**
//      * User yang meminta verifikasi
//      *
//      * @var \App\Models\User
//      */
//     public $user;

//     /**
//      * Link untuk verifikasi user
//      *
//      * @var string
//      */
//     public $verificationUrl;

//     /**
//      * Create a new message instance.
//      *
//      * @param  \App\Models\User  $user
//      * @return void
//      */
//     public function __construct(User $user)
//     {
//         $this->user = $user;
        
//         // Buat URL verifikasi yang hanya dapat diakses oleh admin
//         $this->verificationUrl = URL::temporarySignedRoute(
//             'admin.verify-user',
//             now()->addDays(7),
//             ['user_id' => $user->id]
//         );
//     }

//     /**
//      * Build the message.
//      *
//      * @return $this
//      */
//     public function build()
//     {
//         return $this->subject('Permintaan Verifikasi Pengguna Baru')
//             ->markdown('emails.user-verification-request', [
//                 'user' => $this->user,
//                 'verificationUrl' => $this->verificationUrl,
//             ]);
//     }
// }