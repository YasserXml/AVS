{{-- resources/views/pengajuann/detail-pengajuan.blade.php; --}}
<div class="space-y-6">
    {{-- Informasi Pengajuan --}}
    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
                <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Informasi Pengajuan</h3>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Yang Mengajukan</p>
                <p class="font-medium text-gray-900 dark:text-gray-100">{{ $record->user->name }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Tanggal Pengajuan</p>
                <p class="font-medium text-gray-900 dark:text-gray-100">
                    {{ \Carbon\Carbon::parse($record->tanggal_pengajuan)->format('d F Y') }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Tanggal Dibutuhkan</p>
                <p class="font-medium text-gray-900 dark:text-gray-100">
                    {{ \Carbon\Carbon::parse($record->tanggal_dibutuhkan)->format('d F Y') }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Status</p>
                <span
                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                    {{ ucfirst($record->status) }}
                </span>
            </div>
        </div>
    </div>

    {{-- Detail Barang --}}
    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-10 h-10 bg-green-100 dark:bg-green-900 rounded-lg flex items-center justify-center">
                <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                </svg>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Detail Barang yang Diajukan</h3>
        </div>

        <div class="space-y-4">
            @forelse($detailBarang as $index => $barang)
                <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                    <div class="flex justify-between items-start mb-2">
                        <h4 class="font-medium text-gray-900 dark:text-gray-100">
                            {{ $barang['nama_barang'] ?? 'Nama tidak tersedia' }}</h4>
                        <span
                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                            {{ $barang['jumlah_barang_diajukan'] ?? 0 }} Unit
                        </span>
                    </div>
                    <p class="text-sm text-gray-700 dark:text-gray-200 mt-2">
                        <strong>Spesifikasi:</strong> {{ $barang['keterangan_barang'] ?? 'Tidak ada keterangan' }}
                    </p>
                </div>
            @empty
                <div class="text-center py-4 text-gray-500 dark:text-gray-400">
                    <p>Tidak ada detail barang yang tersedia.</p>
                </div>
            @endforelse
        </div>
    </div>

    {{-- File Pendukung --}}
    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-10 h-10 bg-orange-100 dark:bg-orange-900 rounded-lg flex items-center justify-center">
                <svg class="w-5 h-5 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.586-6.586a4 4 0 00-5.656-5.656l-6.586 6.586a6 6 0 108.486 8.486L20.5 13" />
                </svg>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">File Pendukung</h3>
        </div>

        @if (!empty($uploadedFiles))
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach ($uploadedFiles as $file)
                    <div
                        class="flex items-center justify-between p-3 border border-gray-200 dark:border-gray-700 rounded-lg">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-gray-100 dark:bg-gray-700 rounded flex items-center justify-center">
                                <svg class="w-4 h-4 text-gray-600 dark:text-gray-400" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ basename($file) }}
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ \Illuminate\Support\Str::upper(pathinfo($file, PATHINFO_EXTENSION)) }}
                                </p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-6 text-gray-500 dark:text-gray-400">
                <div
                    class="w-16 h-16 mx-auto mb-4 bg-gray-100 dark:bg-gray-700 rounded-lg flex items-center justify-center">
                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <p class="text-sm">Tidak ada file pendukung yang di-upload.</p>
            </div>
        @endif
    </div>


    <script>
        function downloadFile(filePath) {
            // Buat form untuk download
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route('download.file') }}';
            form.style.display = 'none';

            // CSRF token
            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = '{{ csrf_token() }}';

            // File path
            const fileInput = document.createElement('input');
            fileInput.type = 'hidden';
            fileInput.name = 'file_path';
            fileInput.value = filePath;

            form.appendChild(csrfToken);
            form.appendChild(fileInput);
            document.body.appendChild(form);
            form.submit();
            document.body.removeChild(form);
        }

        // Debug function untuk melihat file path
        function debugFile(filePath) {
            console.log('File path:', filePath);
            console.log('Preview URL:', '{{ route('preview.file') }}?file_path=' + encodeURIComponent(filePath));
        }
    </script>
