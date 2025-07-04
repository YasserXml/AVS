{{-- resources/views/pengajuann/oprasional.blade.php --}}
<div class="fi-ta-content relative overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
    
    {{-- Header --}}
    <div class="fi-ta-header-ctn">
        <div class="fi-ta-header-toolbar flex flex-col gap-3 p-4 sm:flex-row sm:items-center sm:justify-between border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center gap-3">
                <div class="flex items-center gap-2">
                    <div class="w-6 h-6 bg-primary-500 rounded-md flex items-center justify-center">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                    <h4 class="text-lg font-semibold text-gray-950 dark:text-white">
                        Daftar Pengajuan Operasional
                    </h4>
                </div>
            </div>
            
            <div class="flex items-center gap-2">
                <div class="px-2 py-1 bg-gray-100 dark:bg-gray-800 rounded-md">
                    <span class="text-xs font-medium text-gray-700 dark:text-gray-300">
                        {{ $this->getTableRecords()->count() }} dari {{ $this->getTableRecords()->total() }} item
                    </span>
                </div>
                
                {{-- Search & Filter --}}
                <div class="fi-ta-search-container">
                    {{-- Filament akan inject search field disini --}}
                </div>
            </div>
        </div>
    </div>

    {{-- Content Cards --}}
    <div class="fi-ta-content-ctn p-4 space-y-4">
        @forelse ($this->getTableRecords() as $record)
            <div class="fi-ta-record">
                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4 hover:shadow-md hover:border-gray-300 dark:hover:border-gray-600 transition-all duration-200">
                    
                    {{-- Card Header --}}
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex items-center gap-3">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-gradient-to-br from-primary-500 to-primary-600 rounded-lg flex items-center justify-center">
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                </div>
                            </div>
                            
                            <div>
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    Yang Mengajukan: <span class="font-medium text-gray-900 dark:text-white">{{ $record->user->name }}</span>
                                </p>
                            </div>
                        </div>
                        
                        {{-- Status Badge --}}
                        <div class="flex items-center gap-2">
                            @php
                                $statusColor = match($record->status) {
                                    'pengajuan_terkirim' => 'bg-warning-50 text-warning-700 ring-warning-600/20 dark:bg-warning-400/10 dark:text-warning-400 dark:ring-warning-400/20',
                                    'diajukan_ke_superadmin' => 'bg-primary-50 text-primary-700 ring-primary-600/20 dark:bg-primary-400/10 dark:text-primary-400 dark:ring-primary-400/20',
                                    'superadmin_approved' => 'bg-purple-50 text-purple-700 ring-purple-600/20 dark:bg-purple-400/10 dark:text-purple-400 dark:ring-purple-400/20',
                                    'superadmin_rejected' => 'bg-purple-50 text-purple-700 ring-purple-600/20 dark:bg-purple-400/10 dark:text-purple-400 dark:ring-purple-400/20',
                                    'pengajuan_dikirim_ke_admin' => 'bg-success-50 text-success-700 ring-success-600/20 dark:bg-success-400/10 dark:text-success-400 dark:ring-success-400/20',
                                    'admin_approved' => 'bg-danger-50 text-danger-700 ring-danger-600/20 dark:bg-danger-400/10 dark:text-danger-400 dark:ring-danger-400/20',
                                    'rejected' => 'bg-warning-50 text-warning-700 ring-warning-600/20 dark:bg-warning-400/10 dark:text-warning-400 dark:ring-warning-400/20',
                                    'processing' => 'bg-success-50 text-success-700 ring-success-600/20 dark:bg-success-400/10 dark:text-success-400 dark:ring-success-400/20',
                                    'ready_pickup' => 'bg-success-50 text-success-700 ring-success-600/20 dark:bg-success-400/10 dark:text-success-400 dark:ring-success-400/20',
                                    'completed' => 'bg-success-50 text-success-700 ring-success-600/20 dark:bg-success-400/10 dark:text-success-400 dark:ring-success-400/20',
                                    'cancelled' => 'bg-gray-100 text-gray-800 ring-gray-500/20 dark:bg-gray-800 dark:text-gray-200 dark:ring-gray-400/20',
                                    default => 'bg-gray-100 text-gray-800 ring-gray-500/20 dark:bg-gray-800 dark:text-gray-200 dark:ring-gray-400/20',
                                };
                                
                                $statusText = match($record->status) {
                                    'pengajuan_terkirim' => 'Pengajuan Terkirim',
                                    'diajukan_ke_superadmin' => 'Sedang Diajukan ke Pengadaan',
                                    'superadmin_approved' => 'Pengadaan menyetujui',
                                    'superadmin_rejected' => 'Pengadaan menolak',
                                    'pengajuan_dikirim_ke_admin' => 'Pengajuan Dikirim ke Admin',
                                    'admin_approved' => 'Admin menyetujui',
                                    'processing' => 'Diproses',
                                    'ready_pickup' => 'Siap Diambil',
                                    'completed' => 'Selesai',
                                    'cancelled' => 'Dibatalkan',
                                    default => $record->status,
                                };
                            @endphp
                            
                            <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium ring-1 ring-inset {{ $statusColor }}">
                                {{ $statusText }}
                            </span>
                        </div>
                    </div>
                    
                    {{-- Date Info Cards --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-4">
                        {{-- Tanggal Pengajuan --}}
                        <div class="bg-success-50 dark:bg-success-500/10 rounded-lg p-3 border border-success-200 dark:border-success-500/20">
                            <div class="flex items-center gap-2">
                                <div class="w-7 h-7 bg-success-500 rounded-md flex items-center justify-center">
                                    <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <div class="text-sm font-semibold text-success-800 dark:text-success-200">
                                        {{ $record->tanggal_pengajuan->format('d/m/Y') }}
                                    </div>
                                    <div class="text-xs text-success-600 dark:text-success-400">
                                        Tanggal Pengajuan
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        {{-- Tanggal Dibutuhkan --}}
                        <div class="bg-warning-50 dark:bg-warning-500/10 rounded-lg p-3 border border-warning-200 dark:border-warning-500/20">
                            <div class="flex items-center gap-2">
                                <div class="w-7 h-7 bg-warning-500 rounded-md flex items-center justify-center">
                                    <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <div class="text-sm font-semibold text-warning-800 dark:text-warning-200">
                                        {{ $record->tanggal_dibutuhkan ? $record->tanggal_dibutuhkan->format('d/m/Y') : 'Tidak ditentukan' }}
                                    </div>
                                    <div class="text-xs text-warning-600 dark:text-warning-400">
                                        Tanggal Dibutuhkan
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    {{-- Detail Barang --}}
                    @if($record->detail_barang && count($record->detail_barang) > 0)
                        <div class="mb-4">
                            <div class="flex items-center gap-2 mb-3">
                                <div class="w-4 h-4 bg-gray-600 dark:bg-gray-400 rounded flex items-center justify-center">
                                    <svg class="w-2.5 h-2.5 text-white dark:text-gray-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                    </svg>
                                </div>
                                <h4 class="text-sm font-semibold text-gray-900 dark:text-white">Detail Barang</h4>
                            </div>
                            <div class="space-y-2">
                                @foreach($record->detail_barang as $barang)
                                    <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg border border-gray-200 dark:border-gray-600">
                                        <div class="flex-1">
                                            <div class="flex items-center gap-2">
                                                <div class="w-6 h-6 bg-gray-400 dark:bg-gray-500 rounded flex items-center justify-center">
                                                    <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                                    </svg>
                                                </div>
                                                <div>
                                                    <div class="text-sm font-medium text-gray-900 dark:text-white">
                                                        {{ $barang['nama_barang'] }}
                                                    </div>
                                                    @if(isset($barang['keterangan_barang']))
                                                        <div class="text-xs text-gray-600 dark:text-gray-400">
                                                            {{ Str::limit($barang['keterangan_barang'], 60) }}
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        <div class="ml-3">
                                            <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-primary-50 text-primary-700 ring-1 ring-inset ring-primary-600/20 dark:bg-primary-400/10 dark:text-primary-400 dark:ring-primary-400/20">
                                                {{ $barang['jumlah_barang_diajukan'] }} unit
                                            </span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                    
                    {{-- File Pendukung --}}
                    @if($record->uploaded_files && count($record->uploaded_files) > 0)
                        <div class="mb-4">
                            <div class="flex items-center gap-2 mb-3">
                                <div class="w-4 h-4 bg-purple-600 dark:bg-purple-400 rounded flex items-center justify-center">
                                    <svg class="w-2.5 h-2.5 text-white dark:text-purple-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path>
                                    </svg>
                                </div>
                                <h4 class="text-sm font-semibold text-gray-900 dark:text-white">File Pendukung</h4>
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-purple-50 text-purple-700 ring-1 ring-inset ring-purple-600/20 dark:bg-purple-400/10 dark:text-purple-400 dark:ring-purple-400/20">
                                    {{ count($record->uploaded_files) }}
                                </span>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                                @foreach($record->uploaded_files as $file)
                                    @php
                                        $fileExtension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                                        $fileName = pathinfo($file, PATHINFO_FILENAME);
                                    @endphp
                                    
                                    <div class="flex items-center gap-2 p-2 bg-gray-50 dark:bg-gray-700 rounded border border-gray-200 dark:border-gray-600">
                                        <div class="w-6 h-6 bg-purple-500 rounded flex items-center justify-center flex-shrink-0">
                                            <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                            </svg>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="text-xs font-medium text-gray-900 dark:text-white truncate">
                                                {{ $fileName }}
                                            </div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400 uppercase">
                                                {{ $fileExtension }}
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-1">
                                            <a href="{{ Storage::url($file) }}" 
                                               target="_blank"
                                               class="inline-flex items-center px-1.5 py-0.5 text-xs font-medium text-primary-600 dark:text-primary-400 hover:text-primary-800 dark:hover:text-primary-300 transition-colors">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                </svg>
                                                Lihat
                                            </a>
                                            <a href="{{ Storage::url($file) }}" 
                                               download
                                               class="inline-flex items-center px-1.5 py-0.5 text-xs font-medium text-success-600 dark:text-success-400 hover:text-success-800 dark:hover:text-success-300 transition-colors">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                </svg>
                                                Unduh
                                            </a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                    
                    {{-- Actions --}}
                    <div class="flex items-center justify-between pt-3 border-t border-gray-200 dark:border-gray-700">
                        <div class="flex items-center gap-1">
                            <svg class="w-3 h-3 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span class="text-xs text-gray-600 dark:text-gray-400">
                                {{ $record->created_at->diffForHumans() }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center py-8">
                <div class="w-12 h-12 bg-gray-100 dark:bg-gray-800 rounded-lg flex items-center justify-center mx-auto mb-4">
                    <svg class="w-6 h-6 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
                <p class="text-gray-500 dark:text-gray-400">Tidak ada data pengajuan operasional</p>
            </div>
        @endforelse
    </div>
</div>