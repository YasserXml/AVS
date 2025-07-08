<div class="space-y-6">
    {{-- Informasi Umum --}}
    <div class="bg-white rounded-lg border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">📋 Informasi Pengajuan</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <span class="text-sm font-medium text-gray-500">Pengaju:</span>
                <p class="text-sm text-gray-900">{{ $record->pengaju ?? 'Tidak diketahui' }}</p>
            </div>
            <div>
                <span class="text-sm font-medium text-gray-500">Tanggal Pengajuan:</span>
                <p class="text-sm text-gray-900">
                    {{ $record->tanggal_pengajuan ? $record->tanggal_pengajuan->format('d M Y') : 'Tidak diketahui' }}
                </p>
            </div>
            <div>
                <span class="text-sm font-medium text-gray-500">Status:</span>
                <p class="text-sm text-gray-900">{{ $record->status ?? 'Belum diketahui' }}</p>
            </div>
            <div>
                <span class="text-sm font-medium text-gray-500">Keterangan:</span>
                <p class="text-sm text-gray-900">{{ $record->keterangan ?? 'Tidak ada keterangan' }}</p>
            </div>
        </div>
    </div>

    {{-- Detail Barang --}}
    <div class="bg-white rounded-lg border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">📦 Detail Barang yang Diajukan</h3>

        @if ($record->detail_barang && is_array($record->detail_barang) && count($record->detail_barang) > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <div class="bg-blue-50 rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center">
                                <span class="text-white font-bold text-sm">{{ count($record->detail_barang) }}</span>
                            </div>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-blue-900">Total Jenis Barang</p>
                            <p class="text-xs text-blue-700">{{ count($record->detail_barang) }} Jenis Barang</p>
                        </div>
                    </div>
                </div>

                <div class="bg-green-50 rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center">
                                <span
                                    class="text-white font-bold text-sm">{{ collect($record->detail_barang)->sum('jumlah_barang_diajukan') }}</span>
                            </div>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-green-900">Total Kuantitas</p>
                            <p class="text-xs text-green-700">
                                {{ collect($record->detail_barang)->sum('jumlah_barang_diajukan') }} Unit</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="space-y-4">
                @foreach ($record->detail_barang as $index => $item)
                    <div class="border border-gray-200 rounded-lg p-4">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <h4 class="font-medium text-gray-900">{{ $index + 1 }}.
                                    {{ $item['nama_barang'] ?? 'Barang tidak diketahui' }}</h4>
                                <p class="text-sm text-gray-600 mt-1">
                                    <span class="font-medium">Jumlah:</span> {{ $item['jumlah_barang_diajukan'] ?? 0 }}
                                    unit
                                </p>
                                @if (isset($item['keterangan_barang']) && $item['keterangan_barang'])
                                    <p class="text-sm text-gray-600 mt-1">
                                        <span class="font-medium">Keterangan:</span> {{ $item['keterangan_barang'] }}
                                    </p>
                                @endif
                            </div>
                            <div class="ml-4">
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    {{ $item['jumlah_barang_diajukan'] ?? 0 }} unit
                                </span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-8">
                <div class="text-gray-400 text-4xl mb-4">📦</div>
                <p class="text-gray-500">Tidak ada detail barang yang diajukan</p>
            </div>
        @endif
    </div>

    {{-- File Pendukung --}}
    <div class="bg-white rounded-lg border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">📎 File Pendukung</h3>

        @if ($record->uploaded_files && is_array($record->uploaded_files) && count($record->uploaded_files) > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach ($record->uploaded_files as $index => $file)
                    @php
                        $fileName = basename($file);
                        $fileUrl = Storage::url($file);
                        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

                        // Determine file icon based on extension
                        $iconClass = match ($fileExtension) {
                            'pdf' => 'text-red-500',
                            'doc', 'docx' => 'text-blue-500',
                            'xls', 'xlsx' => 'text-green-500',
                            'jpg', 'jpeg', 'png', 'gif' => 'text-purple-500',
                            default => 'text-gray-500',
                        };

                        $iconName = match ($fileExtension) {
                            'pdf' => 'document-text',
                            'doc', 'docx' => 'document-text',
                            'xls', 'xlsx' => 'table-cells',
                            'jpg', 'jpeg', 'png', 'gif' => 'photo',
                            default => 'document',
                        };
                    @endphp

                    <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                        <div class="flex items-start space-x-3">
                            <div class="flex-shrink-0">
                                <x-heroicon-o-{{ $iconName }} class="w-8 h-8 {{ $iconClass }}" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 truncate" title="{{ $fileName }}">
                                    {{ $fileName }}
                                </p>
                                <p class="text-xs text-gray-500 mt-1">
                                    File {{ $index + 1 }}
                                </p>
                                <div class="mt-2">
                                    <a href="{{ $fileUrl }}" target="_blank" download="{{ $fileName }}"
                                        class="inline-flex items-center px-3 py-1 text-xs font-medium text-blue-700 bg-blue-50 rounded-full hover:bg-blue-100 transition-colors">
                                        <x-heroicon-o-arrow-down-tray class="w-3 h-3 mr-1" />
                                        Download
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-8">
                <div class="text-gray-400 text-4xl mb-4">📎</div>
                <p class="text-gray-500">Tidak ada file pendukung</p>
            </div>
        @endif
    </div>
</div>
