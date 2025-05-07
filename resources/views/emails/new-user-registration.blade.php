<x-mail::message>
# Pendaftaran Pengguna Baru

Halo Admin,

Pengguna baru telah mendaftar pada aplikasi dan membutuhkan verifikasi Anda.

**Detail pengguna:**
- **Nama:** {{ $user->name }}
- **Email:** {{ $user->email }}
- **Pendaftaran:** {{ $user->created_at->format('d M Y H:i') }}
- **Metode Pendaftaran:** {{ $user->provider ? ucfirst($user->provider) : 'Form Pendaftaran' }}

Silakan pilih salah satu tindakan di bawah ini:

<x-mail::button :url="$verifyUrl" color="success">
Verifikasi Pengguna
</x-mail::button>

<x-mail::button :url="$rejectUrl" color="error">
Tolak Pengguna 
</x-mail::button>

Atau, Anda dapat mengelola semua pengguna melalui panel admin:

<x-mail::button :url="$userManagementUrl">
Kelola Pengguna
</x-mail::button>

Terima kasih,<br>
{{ config('app.name') }}
</x-mail::message>