<x-mail::message>
# Akun Anda Telah Diverifikasi

Halo {{ $user->name }},

Selamat! Akun Anda telah diverifikasi oleh admin dan sekarang Anda dapat masuk ke dalam sistem.

**Detail akun:**
- **Nama:** {{ $user->name }}
- **Email:** {{ $user->email }}

Silakan klik tombol di bawah ini untuk login:

<x-mail::button :url="$loginUrl">
    Masuk ke Sistem
</x-mail::button>

Terima kasih telah bergabung dengan kami!

Salam,<br>
{{ config('app.name') }}
</x-mail::message>