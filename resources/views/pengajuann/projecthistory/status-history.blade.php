@php
    // Helper methods untuk menampilkan status project
    if (!function_exists('getProjectStatusIcon')) {
        function getProjectStatusIcon($status) {
            $icons = [
                'pengajuan_terkirim' => '<x-heroicon-o-paper-airplane class="w-5 h-5 text-blue-500" />',
                'pending_pm_review' => '<x-heroicon-o-eye class="w-5 h-5 text-yellow-500" />',
                'disetujui_pm_dikirim_ke_pengadaan' => '<x-heroicon-o-arrow-up-tray class="w-5 h-5 text-orange-500" />',
                'ditolak_pm' => '<x-heroicon-o-x-circle class="w-5 h-5 text-red-500" />',
                'disetujui_pengadaan' => '<x-heroicon-o-check-circle class="w-5 h-5 text-green-500" />',
                'ditolak_pengadaan' => '<x-heroicon-o-x-circle class="w-5 h-5 text-red-500" />',
                'pengajuan_dikirim_ke_direksi' => '<x-heroicon-o-arrow-up-tray class="w-5 h-5 text-purple-500" />',
                'approved_by_direksi' => '<x-heroicon-o-check-circle class="w-5 h-5 text-green-500" />',
                'reject_direksi' => '<x-heroicon-o-x-circle class="w-5 h-5 text-red-500" />',
                'pengajuan_dikirim_ke_keuangan' => '<x-heroicon-o-arrow-up-tray class="w-5 h-5 text-indigo-500" />',
                'pending_keuangan' => '<x-heroicon-o-clock class="w-5 h-5 text-yellow-500" />',
                'process_keuangan' => '<x-heroicon-o-cog-6-tooth class="w-5 h-5 text-blue-500" />',
                'execute_keuangan' => '<x-heroicon-o-bolt class="w-5 h-5 text-purple-500" />',
                'pengajuan_dikirim_ke_pengadaan_final' => '<x-heroicon-o-arrow-up-tray class="w-5 h-5 text-orange-500" />',
                'pengajuan_dikirim_ke_admin' => '<x-heroicon-o-arrow-up-tray class="w-5 h-5 text-gray-500" />',
                'processing' => '<x-heroicon-o-cog-6-tooth class="w-5 h-5 text-purple-500" />',
                'ready_pickup' => '<x-heroicon-o-inbox-arrow-down class="w-5 h-5 text-teal-500" />',
                'completed' => '<x-heroicon-o-check-badge class="w-5 h-5 text-green-600" />',
            ];
            
            return $icons[$status] ?? '<x-heroicon-o-information-circle class="w-5 h-5 text-gray-500" />';
        }
    }

    if (!function_exists('getProjectStatusLabel')) {
        function getProjectStatusLabel($status) {
            $labels = [
                'pengajuan_terkirim' => 'Pengajuan Terkirim',
                'pending_pm_review' => 'Menunggu Review PM',
                'disetujui_pm_dikirim_ke_pengadaan' => 'Disetujui PM - Dikirim ke Pengadaan',
                'ditolak_pm' => 'Ditolak PM',
                'disetujui_pengadaan' => 'Disetujui Pengadaan',
                'ditolak_pengadaan' => 'Ditolak Pengadaan',
                'pengajuan_dikirim_ke_direksi' => 'Dikirim ke Direksi',
                'approved_by_direksi' => 'Disetujui Direksi',
                'reject_direksi' => 'Ditolak Direksi',
                'pengajuan_dikirim_ke_keuangan' => 'Dikirim ke Keuangan',
                'pending_keuangan' => 'Menunggu Keuangan',
                'process_keuangan' => 'Diproses Keuangan',
                'execute_keuangan' => 'Dieksekusi Keuangan',
                'pengajuan_dikirim_ke_pengadaan_final' => 'Dikirim ke Pengadaan Final',
                'pengajuan_dikirim_ke_admin' => 'Dikirim ke Admin',
                'processing' => 'Sedang Diproses',
                'ready_pickup' => 'Siap Diambil',
                'completed' => 'Selesai',
            ];
            
            return $labels[$status] ?? ucfirst(str_replace('_', ' ', $status));
        }
    }

    if (!function_exists('getProjectStatusBadgeColor')) {
        function getProjectStatusBadgeColor($status) {
            $colors = [
                'pengajuan_terkirim' => 'bg-blue-100 text-blue-800 dark:bg-blue-800 dark:text-blue-100',
                'pending_pm_review' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-800 dark:text-yellow-100',
                'disetujui_pm_dikirim_ke_pengadaan' => 'bg-orange-100 text-orange-800 dark:bg-orange-800 dark:text-orange-100',
                'ditolak_pm' => 'bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100',
                'disetujui_pengadaan' => 'bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100',
                'ditolak_pengadaan' => 'bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100',
                'pengajuan_dikirim_ke_direksi' => 'bg-purple-100 text-purple-800 dark:bg-purple-800 dark:text-purple-100',
                'approved_by_direksi' => 'bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100',
                'reject_direksi' => 'bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100',
                'pengajuan_dikirim_ke_keuangan' => 'bg-indigo-100 text-indigo-800 dark:bg-indigo-800 dark:text-indigo-100',
                'pending_keuangan' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-800 dark:text-yellow-100',
                'process_keuangan' => 'bg-blue-100 text-blue-800 dark:bg-blue-800 dark:text-blue-100',
                'execute_keuangan' => 'bg-purple-100 text-purple-800 dark:bg-purple-800 dark:text-purple-100',
                'pengajuan_dikirim_ke_pengadaan_final' => 'bg-orange-100 text-orange-800 dark:bg-orange-800 dark:text-orange-100',
                'pengajuan_dikirim_ke_admin' => 'bg-gray-100 text-gray-800 dark:bg-gray-600 dark:text-gray-100',
                'processing' => 'bg-purple-100 text-purple-800 dark:bg-purple-800 dark:text-purple-100',
                'ready_pickup' => 'bg-teal-100 text-teal-800 dark:bg-teal-800 dark:text-teal-100',
                'completed' => 'bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100',
            ];
            
            return $colors[$status] ?? 'bg-gray-100 text-gray-800 dark:bg-gray-600 dark:text-gray-100';
        }
    }

    // Mengatur timezone ke Asia/Jakarta dan locale Indonesia
    \Carbon\Carbon::setLocale('id');
    date_default_timezone_set('Asia/Jakarta');
@endphp

<div class="space-y-4">
    <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
        <div class="flex items-center space-x-2 mb-3">
            <x-heroicon-o-information-circle class="w-5 h-5 text-blue-500" />
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                Riwayat Perubahan Status Pengajuan Barang Project
            </h3>
        </div>
        <p class="text-sm text-gray-400 dark:text-gray-400">
            Berikut adalah riwayat lengkap perubahan status pengajuan project ini.
        </p>
    </div>

    <div class="relative">
        {{-- Timeline line --}}
        <div class="absolute left-6 top-0 bottom-0 w-0.5 bg-gray-300 dark:bg-gray-600"></div>
        
        @foreach($history as $index => $item)
            <div class="relative flex items-start space-x-4 pb-6 {{ $loop->last ? 'pb-0' : '' }}">
                {{-- Timeline icon --}}
                <div class="relative z-10 flex items-center justify-center">
                    <div class="w-12 h-12 rounded-full flex items-center justify-center {{ $index === 0 ? 'bg-blue-100 dark:bg-blue-800 border-2 border-blue-500' : 'bg-white dark:bg-gray-700 border-2 border-gray-300 dark:border-gray-600' }}">
                        {!! getProjectStatusIcon($item['status']) !!}
                    </div>
                </div>

                {{-- Content --}}
                <div class="flex-1 min-w-0">
                    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-600 p-4 {{ $index === 0 ? 'ring-2 ring-blue-500 ring-opacity-50 dark:ring-opacity-50' : '' }}">
                        {{-- Header --}}
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center space-x-2 flex-wrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ getProjectStatusBadgeColor($item['status']) }}">
                                    {{ getProjectStatusLabel($item['status']) }}
                                </span>
                                @if($index === 0)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-800 dark:text-blue-100">
                                        Status Terkini
                                    </span>
                                @endif
                            </div>
                            <time class="text-xs text-gray-500 dark:text-gray-400 whitespace-nowrap ml-2">
                                {{ \Carbon\Carbon::parse($item['created_at'])->setTimezone('Asia/Jakarta')->format('d M Y, H:i') }} WIB
                            </time>
                        </div>

                        {{-- Note --}}
                        <p class="text-sm text-gray-900 dark:text-gray-100 mb-3 leading-relaxed font-medium">
                            {{ $item['note'] ?? 'Tidak ada catatan' }}
                        </p>

                        {{-- Additional Info for Project Status --}}
                        @if(isset($item['additional_info']))
                            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3 mb-3 border border-gray-100 dark:border-gray-600">
                                <h4 class="text-xs font-semibold text-gray-700 dark:text-gray-300 mb-2">Informasi Tambahan:</h4>
                                <p class="text-xs text-gray-800 dark:text-gray-200 leading-relaxed">{{ $item['additional_info'] }}</p>
                            </div>
                        @endif

                        {{-- Rejection reason if exists --}}
                        @if(in_array($item['status'], ['ditolak_pm', 'ditolak_pengadaan', 'reject_direksi']) && isset($item['reject_reason']))
                            <div class="bg-red-50 dark:bg-red-900/30 rounded-lg p-3 mb-3 border border-red-200 dark:border-red-800">
                                <div class="flex items-center space-x-2 mb-2">
                                    <x-heroicon-o-exclamation-triangle class="w-4 h-4 text-red-500 dark:text-red-400" />
                                    <h4 class="text-xs font-semibold text-red-700 dark:text-red-300">Alasan Penolakan:</h4>
                                </div>
                                <p class="text-xs text-red-800 dark:text-red-200 leading-relaxed">{{ $item['reject_reason'] }}</p>
                            </div>
                        @endif

                        {{-- Department/Role based on status --}}
                        <div class="flex items-center justify-between pt-2 border-t border-gray-100 dark:border-gray-700">
                            <div class="flex items-center space-x-2 text-xs text-gray-700 dark:text-gray-200">
                                <x-heroicon-o-user class="w-4 h-4" />
                                <span class="font-semibold">
                                    @php
                                        $user = \App\Models\User::find($item['user_id']);
                                    @endphp
                                    {{ $user ? $user->name : 'Pengguna tidak diketahui' }}
                                </span>
                                <span class="text-gray-400 dark:text-gray-400">•</span>
                                <span class="text-gray-400 dark:text-gray-400">{{ \Carbon\Carbon::parse($item['created_at'])->setTimezone('Asia/Jakarta')->diffForHumans() }}</span>
                            </div>
                            
                            {{-- Department badge based on status --}}
                            <div class="flex items-center space-x-1">
                                @php
                                    $department = match($item['status']) {
                                        'pengajuan_terkirim' => ['PM', 'bg-blue-100 text-blue-800 dark:bg-blue-800 dark:text-blue-100'],
                                        'pending_pm_review' => ['PM', 'bg-yellow-100 text-yellow-800 dark:bg-yellow-800 dark:text-yellow-100'],
                                        'disetujui_pm_dikirim_ke_pengadaan', 'ditolak_pm' => ['PM', 'bg-orange-100 text-orange-800 dark:bg-orange-800 dark:text-orange-100'],
                                        'disetujui_pengadaan', 'ditolak_pengadaan' => ['Pengadaan', 'bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100'],
                                        'pengajuan_dikirim_ke_direksi', 'approved_by_direksi', 'reject_direksi' => ['Direksi', 'bg-purple-100 text-purple-800 dark:bg-purple-800 dark:text-purple-100'],
                                        'pengajuan_dikirim_ke_keuangan', 'pending_keuangan', 'process_keuangan', 'execute_keuangan' => ['Keuangan', 'bg-indigo-100 text-indigo-800 dark:bg-indigo-800 dark:text-indigo-100'],
                                        'pengajuan_dikirim_ke_pengadaan_final' => ['Pengadaan', 'bg-orange-100 text-orange-800 dark:bg-orange-800 dark:text-orange-100'],
                                        'pengajuan_dikirim_ke_admin', 'processing', 'ready_pickup', 'completed' => ['Admin', 'bg-gray-100 text-gray-800 dark:bg-gray-600 dark:text-gray-100'],
                                        default => ['System', 'bg-gray-100 text-gray-800 dark:bg-gray-600 dark:text-gray-100'],
                                    };
                                @endphp
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $department[1] }}">
                                    {{ $department[0] }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>