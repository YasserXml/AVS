@component('mail::message')
# Pengajuan Dikirim ke Tim Pengadaan

Halo {{ $pengaju->name }},

Pengajuan Anda dengan ID **#{{ $pengajuan->id }}** telah dikirim ke tim pengadaan untuk persetujuan.

## Detail Pengajuan
- **ID Pengajuan**: #{{ $pengajuan->id }}
- **Tanggal Pengajuan**: {{ $pengajuan->created_at->format('d/m/Y H:i') }}
- **Status**: Menunggu Persetujuan Tim Pengadaan

Anda akan menerima pemberitahuan lebih lanjut setelah tim pengadaan melakukan review.

@component('mail::button', ['url' => config('app.url')])
Lihat Pengajuan
@endcomponent

Terima kasih,
{{ config('app.name') }}
@endcomponent