{{-- @component('mail::message')
# Permintaan Verifikasi Pengguna Baru

Halo Admin,

Ada permintaan verifikasi untuk pengguna baru di sistem Anda.

**Detail Pengguna:**
- Nama: {{ $user->name }}
- Email: {{ $user->email }}
- Tanggal Pendaftaran: {{ $user->created_at->format('d/m/Y H:i') }}

Untuk memverifikasi pengguna ini, silakan klik tombol di bawah:

@component('mail::button', ['url' => $verificationUrl])
Verifikasi Pengguna
@endcomponent

Link verifikasi ini akan kedaluwarsa dalam 7 hari.

Terima kasih,<br>
{{ config('app.name') }}
@endcomponent --}}