{{-- <x-mail::message>
# Informasi Penolakan Pendaftaran

Halo {{ $user->name }},

Dengan berat hati kami informasikan bahwa pendaftaran akun Anda tidak dapat disetujui oleh admin saat ini.

**Detail akun:**
- **Nama:** {{ $user->name }}
- **Email:** {{ $user->email }}

Jika Anda merasa ini adalah kesalahan atau memiliki pertanyaan lebih lanjut, silakan hubungi tim dukungan kami melalui email di {{ config('mail.from.address') }}.

Terima kasih atas minat Anda untuk bergabung dengan kami.

Salam,<br>
{{ config('app.name') }}
</x-mail::message> --}}