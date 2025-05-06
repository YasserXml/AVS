@component('mail::message')
<div style="text-align: center; margin-bottom: 20px;">
    <img src="{{ asset('icon.png') }}" alt="Logo" style="max-width: 100px; height: auto;">
    <h1 style="margin-top: 10px;">{{ config('app.name') }}</h1>
</div>

# Pendaftaran Pengguna Baru

@component('mail::panel')
Pendaftaran dari: **{{ $newUser->name }}**  
Email: **{{ $newUser->email }}**  
Tanggal Pendaftaran: **{{ $newUser->created_at->format('d F Y H:i') }}**
@endcomponent

## Informasi Pengguna

@component('mail::table')
| Informasi | Detail |
|:----------|:-------|
| Nama Pengguna: | {{ $newUser->name }} |
| Email: | {{ $newUser->email }} |
@endcomponent

Pendaftaran pengguna baru memerlukan verifikasi dari administrator.

@component('mail::button', ['url' => $verifyUrl, 'color' => 'success'])
Verifikasi Pengguna
@endcomponent

@component('mail::button', ['url' => $rejectUrl, 'color' => 'error'])
Tolak Pendaftaran
@endcomponent

Terima kasih,<br>
{{ config('app.name') }}

---
<small>
Ini adalah email otomatis. Mohon tidak membalas email ini.
</small>
@endcomponent