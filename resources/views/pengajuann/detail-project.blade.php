{{-- resources/views/pengajuan-project/detail-pengajuan-project.blade.php --}}
<x-filament::section>
    <x-slot name="heading">
        <div class="flex items-center gap-2">
            <x-filament::icon icon="heroicon-o-document-text" class="w-5 h-5 text-primary-500" />
            Detail Pengajuan Project
        </div>
    </x-slot>
    <div class="space-y-6">
        {{-- Informasi Project --}}
        <x-filament::section>
            <x-slot name="heading">
                <div class="flex items-center gap-2">
                    <x-filament::icon icon="heroicon-o-building-office" class="w-5 h-5 text-info-500" />
                    Informasi Project
                </div>
            </x-slot>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-4">
                    <x-filament::section compact class="bg-gray-50 dark:bg-gray-800/50">
                        <div class="space-y-3">
                            <div class="flex justify-between items-center">
                                <span class="text-sm font-medium text-gray-600 dark:text-gray-200">Tanggal
                                    Pengajuan</span>
                                <span class="text-sm text-gray-900 dark:text-white">
                                    {{ \Carbon\Carbon::parse($record->tanggal_pengajuan)->format('d F Y') }}
                                </span>
                            </div>
                            <div class="flex justify-between items-center">
                                 <span class="text-sm font-medium text-gray-600 dark:text-gray-200">Tanggal
                                    Dibutuhkan</span>
                                 <span class="text-sm text-gray-900 dark:text-white">
                                    {{ \Carbon\Carbon::parse($record->tanggal_dibutuhkan)->format('d F Y') }}
                                </span>
                            </div>
                            <div class="flex justify-between items-center">
                                 <span class="text-sm font-medium text-gray-600 dark:text-gray-200">Nama Project</span>
                                <span class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                    {{ $record->nameproject->nama_project ?? 'Tidak ada' }}
                                </span>
                            </div>
                        </div>
                    </x-filament::section>
                </div>

                <div class="space-y-4">
                    <x-filament::section compact class="bg-gray-50 dark:bg-gray-800/50">
                        <div class="space-y-3">
                            <div class="flex justify-between items-center">
                                 <span class="text-sm font-medium text-gray-600 dark:text-gray-200">Project
                                    Manager</span>
                                 <span class="text-sm text-gray-900 dark:text-white">
                                    {{ $record->nameproject->user->name ?? 'Tidak ada PM' }}
                                </span>
                            </div>
                            <div class="flex justify-between items-center">
                                 <span class="text-sm font-medium text-gray-600 dark:text-gray-200">Pengaju</span>
                                 <span class="text-sm text-gray-900 dark:text-white">
                                    {{ $record->user->name ?? 'Tidak diketahui' }}
                                </span>
                            </div>
                            <div class="flex justify-between items-center">
                                 <span class="text-sm font-medium text-gray-600 dark:text-gray-200">Status</span>
                                <x-filament::badge :color="$record->status === 'pending' ? 'warning' : ($record->status === 'approved' ? 'success' : 'danger')">
                                    {{ ucfirst($record->status) }}
                                </x-filament::badge>
                            </div>
                        </div>
                    </x-filament::section>
                </div>
            </div>
        </x-filament::section>

        {{-- Detail Barang Project --}}
        @if (!empty($detailBarang))
            <x-filament::section>
                <x-slot name="heading">
                    <div class="flex items-center gap-2">
                        <x-filament::icon icon="heroicon-o-cube" class="w-5 h-5 text-success-500" />
                        Detail Barang Project
                        <x-filament::badge color="success">
                            {{ count($detailBarang) }} item
                        </x-filament::badge>
                    </div>
                </x-slot>

                <div class="space-y-4">
                    @foreach ($detailBarang as $index => $barang)
                        <x-filament::section compact
                            class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700">
                            <div class="space-y-4">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                                            {{ $barang['nama_barang'] ?? 'Nama barang tidak tersedia' }}
                                        </h4>
                                        <div class="flex items-center gap-4">
                                            <div class="flex items-center gap-1">
                                                <x-filament::icon icon="heroicon-o-calculator"
                                                    class="w-4 h-4 text-gray-500 dark:text-gray-400" />
                                                <span class="text-sm text-gray-600 dark:text-gray-200">Jumlah:</span>
                                                <span class="text-sm font-medium text-gray-900 dark:text-white">
                                                    {{ $barang['jumlah_barang_diajukan'] ?? 0 }} unit
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Spesifikasi Barang --}}
                                @if (!empty($barang['keterangan_barang']))
                                    <div>
                                        <h5 class="text-sm font-medium text-gray-900 dark:text-white mb-2">
                                            Spesifikasi:
                                        </h5>
                                        <x-filament::section compact
                                            class="bg-gray-50 dark:bg-gray-800/50 border border-gray-200 dark:border-gray-700">
                                            <p class="text-sm text-gray-700 dark:text-gray-200 leading-relaxed">
                                                {{ $barang['keterangan_barang'] }}
                                            </p>
                                        </x-filament::section>
                                    </div>
                                @endif

                                {{-- File Barang --}}
                                @if (!empty($barang['file_barang']))
                                    <div>
                                        <h5 class="text-sm font-medium text-gray-900 dark:text-white mb-3">
                                            File Pendukung Barang:
                                        </h5>
                                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                                            @foreach ($barang['file_barang'] as $file)
                                                <x-filament::section compact
                                                    class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700">
                                                    @php
                                                        $extension = pathinfo($file, PATHINFO_EXTENSION);
                                                        $isImage = in_array(strtolower($extension), [
                                                            'jpg',
                                                            'jpeg',
                                                            'png',
                                                            'gif',
                                                            'webp',
                                                        ]);
                                                    @endphp

                                                    <div class="space-y-2">
                                                        <div class="flex items-center justify-between">
                                                            <div class="flex items-center gap-2 flex-1 min-w-0">
                                                                @if ($isImage)
                                                                    <x-filament::icon icon="heroicon-o-photo"
                                                                        class="w-4 h-4 text-success-500 flex-shrink-0" />
                                                                @else
                                                                    <x-filament::icon icon="heroicon-o-document"
                                                                        class="w-4 h-4 text-info-500 flex-shrink-0" />
                                                                @endif
                                                                <span
                                                                    class="text-sm font-medium text-gray-900 dark:text-white truncate"
                                                                    title="{{ basename($file) }}">
                                                                    {{ basename($file) }}
                                                                </span>
                                                            </div>

                                                            <div class="flex items-center gap-1">
                                                                {{-- Preview Button - Buka di tab baru --}}
                                                                <a href="{{ route('preview.project.file', ['file_path' => $file]) }}"
                                                                    target="_blank"
                                                                    class="inline-flex items-center justify-center w-8 h-8 rounded-md bg-info-500 hover:bg-info-600 text-white transition-colors"
                                                                    title="Preview File"
                                                                    onclick="return handlePreview(this)">
                                                                    <x-filament::icon icon="heroicon-o-eye"
                                                                        class="w-4 h-4" />
                                                                </a>

                                                                {{-- Download Button --}}
                                                                <form action="{{ route('download.project.file') }}"
                                                                    method="POST" class="inline">
                                                                    @csrf
                                                                    <input type="hidden" name="file_path"
                                                                        value="{{ $file }}">
                                                                    <button type="submit"
                                                                        class="inline-flex items-center justify-center w-8 h-8 rounded-md bg-success-500 hover:bg-success-600 text-white transition-colors"
                                                                        title="Download File">
                                                                        <x-filament::icon
                                                                            icon="heroicon-o-arrow-down-tray"
                                                                            class="w-4 h-4" />
                                                                    </button>
                                                                </form>
                                                            </div>
                                                        </div>

                                                        @if ($isImage)
                                                            <div
                                                                class="rounded-lg overflow-hidden border border-gray-200 dark:border-gray-700">
                                                                <img src="{{ asset('storage/' . $file) }}"
                                                                    alt="Preview" class="w-full h-32 object-cover">
                                                            </div>
                                                        @endif
                                                    </div>
                                                </x-filament::section>
                                            @endforeach
                                        </div>

                                        @if (count($barang['file_barang']) > 1)
                                            <div class="mt-4">
                                                <form action="{{ route('download.project.barang') }}" method="POST"
                                                    class="inline">
                                                    @csrf
                                                    <input type="hidden" name="files"
                                                        value="{{ json_encode($barang['file_barang']) }}">
                                                    <input type="hidden" name="barang_name"
                                                        value="{{ $barang['nama_barang'] }}">
                                                    <x-filament::button color="success"
                                                        icon="heroicon-o-arrow-down-tray" size="sm"
                                                        type="submit">
                                                        Download Semua File Barang
                                                    </x-filament::button>
                                                </form>
                                            </div>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </x-filament::section>
                    @endforeach
                </div>
            </x-filament::section>
        @endif

        {{-- File Pendukung Project --}}
        @if (!empty($uploadedFiles))
            <x-filament::section>
                <x-slot name="heading">
                    <div class="flex items-center gap-2">
                        <x-filament::icon icon="heroicon-o-paper-clip" class="w-5 h-5 text-primary-500" />
                        File Pendukung Project
                        <x-filament::badge color="primary">
                            {{ count($uploadedFiles) }} file
                        </x-filament::badge>
                    </div>
                </x-slot>

                <div class="space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                        @foreach ($uploadedFiles as $file)
                            <x-filament::section compact
                                class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700">
                                @php
                                    $extension = pathinfo($file, PATHINFO_EXTENSION);
                                    $isImage = in_array(strtolower($extension), ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                                    $isPdf = strtolower($extension) === 'pdf';
                                @endphp

                                <div class="space-y-2">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2 flex-1 min-w-0">
                                            @if ($isImage)
                                                <x-filament::icon icon="heroicon-o-photo"
                                                    class="w-4 h-4 text-success-500 flex-shrink-0" />
                                            @elseif($isPdf)
                                                <x-filament::icon icon="heroicon-o-document-text"
                                                    class="w-4 h-4 text-danger-500 flex-shrink-0" />
                                            @else
                                                <x-filament::icon icon="heroicon-o-document"
                                                    class="w-4 h-4 text-info-500 flex-shrink-0" />
                                            @endif
                                            <span class="text-sm font-medium text-gray-900 dark:text-white truncate"
                                                title="{{ basename($file) }}">
                                                {{ basename($file) }}
                                            </span>
                                        </div>

                                        <div class="flex items-center gap-1">
                                            {{-- Preview Button - Gunakan link langsung --}}
                                            <a href="{{ route('preview.project.file', ['file_path' => $file]) }}"
                                                target="_blank"
                                                class="inline-flex items-center justify-center w-8 h-8 rounded-md bg-info-500 hover:bg-info-600 text-white transition-colors"
                                                title="Preview File" onclick="return handlePreview(this)">
                                                <x-filament::icon icon="heroicon-o-eye" class="w-4 h-4" />
                                            </a>

                                            {{-- Download Button --}}
                                            <form action="{{ route('download.project.file') }}" method="POST"
                                                class="inline">
                                                @csrf
                                                <input type="hidden" name="file_path" value="{{ $file }}">
                                                <button type="submit"
                                                    class="inline-flex items-center justify-center w-8 h-8 rounded-md bg-success-500 hover:bg-success-600 text-white transition-colors"
                                                    title="Download File">
                                                    <x-filament::icon icon="heroicon-o-arrow-down-tray"
                                                        class="w-4 h-4" />
                                                </button>
                                            </form>
                                        </div>
                                    </div>

                                    @if ($isImage)
                                        <div
                                            class="rounded-lg overflow-hidden border border-gray-200 dark:border-gray-700">
                                            <img src="{{ asset('storage/' . $file) }}" alt="Preview"
                                                class="w-full h-32 object-cover">
                                        </div>
                                    @endif
                                </div>
                            </x-filament::section>
                        @endforeach
                    </div>
                </div>
            </x-filament::section>
        @endif
    </div>
</x-filament::section>

{{-- Print Styles --}}
<style>
    @media print {
        .print\\:hidden {
            display: none !important;
        }

        body {
            font-size: 12px;
        }

        .dark\\:bg-gray-800\\/50 {
            background-color: #f8f9fa !important;
        }

        .rounded-lg {
            border: 1px solid #e5e7eb !important;
        }
    }

    /* Custom Dark Mode Fixes */
    .dark .file-name-text {
        color: #ffffff !important;
    }

    .dark .spesifikasi-text {
        color: #e5e7eb !important;
    }

    .dark .section-title {
        color: #ffffff !important;
    }
</style>

<script>
    function handlePreview(link) {
        const url = link.href;

        // Buka di tab baru
        window.open(url, '_blank');

        // Prevent default action
        return false;
    }
</script>
