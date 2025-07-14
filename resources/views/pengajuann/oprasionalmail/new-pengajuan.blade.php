<x-mail::message>
# Pengajuan Barang Baru

Halo {{ $admin->name }},

Pengajuan barang baru telah diterima dengan detail sebagai berikut:

**Informasi Pengajuan:**
- **Nama Pengaju:** {{ $pengajuan->user->name }}
- **Tanggal Pengajuan:** {{ $pengajuan->tanggal_pengajuan->format('d F Y') }}
- **Tanggal Dibutuhkan:** {{ $pengajuan->tanggal_dibutuhkan->format('d F Y') }}
- **Jumlah Item:** {{ count($pengajuan->detail_barang) }} item

**Detail Barang:**
@foreach($pengajuan->detail_barang as $item)
- **{{ $item['nama_barang'] }}**
  - Jumlah: {{ $item['jumlah_barang_diajukan'] }} unit
  - Spesifikasi: {{ $item['keterangan_barang'] }}
@endforeach

<x-mail::button :url="route('filament.admin.resources.permintaan.pengajuan-oprasional.index', ['record' => $pengajuan->id])">
Lihat Detail Pengajuan
</x-mail::button>

Silakan login ke sistem untuk memproses pengajuan ini.

Terima kasih,<br>
{{ config('app.name') }}
</x-mail::message>