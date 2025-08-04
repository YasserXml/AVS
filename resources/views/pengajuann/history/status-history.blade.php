{{-- resource/views/pengajuann/history/status-history.blade.php --}}
@php
    // Helper methods untuk menampilkan status pengajuan operasional
    if (!function_exists('getStatusIcon')) {
        function getStatusIcon($status) {
            $icons = [
                'pengajuan_terkirim' => '<x-heroicon-o-paper-airplane class="w-5 h-5 text-blue-500" />',
                'pending_admin_review' => '<x-heroicon-o-eye class="w-5 h-5 text-yellow-500" />',
                'diajukan_ke_superadmin' => '<x-heroicon-o-arrow-up-tray class="w-5 h-5 text-orange-500" />',
                'superadmin_approved' => '<x-heroicon-o-check-circle class="w-5 h-5 text-green-500" />',
                'superadmin_rejected' => '<x-heroicon-o-x-circle class="w-5 h-5 text-red-500" />',
                'pengajuan_dikirim_ke_direksi' => '<x-heroicon-o-arrow-up-tray class="w-5 h-5 text-purple-500" />',
                'pending_direksi' => '<x-heroicon-o-pause class="w-5 h-5 text-yellow-500" />',
                'approved_by_direksi' => '<x-heroicon-o-check-circle class="w-5 h-5 text-green-500" />',
                'approved_at_direksi' => '<x-heroicon-o-check-badge class="w-5 h-5 text-green-600" />',
                'reject_direksi' => '<x-heroicon-o-x-circle class="w-5 h-5 text-red-500" />',
                'pengajuan_dikirim_ke_keuangan' => '<x-heroicon-o-arrow-up-tray class="w-5 h-5 text-indigo-500" />',
                'pending_keuangan' => '<x-heroicon-o-clock class="w-5 h-5 text-yellow-500" />',
                'process_keuangan' => '<x-heroicon-o-cog-6-tooth class="w-5 h-5 text-blue-500" />',
                'execute_keuangan' => '<x-heroicon-o-bolt class="w-5 h-5 text-purple-500" />',
                'executed_by_keuangan' => '<x-heroicon-o-check-badge class="w-5 h-5 text-purple-600" />',
                'executed_at_keuangan' => '<x-heroicon-o-check-badge class="w-5 h-5 text-purple-700" />',
                'pengajuan_dikirim_ke_pengadaan' => '<x-heroicon-o-arrow-up-tray class="w-5 h-5 text-orange-500" />',
                'pengajuan_dikirim_ke_admin' => '<x-heroicon-o-arrow-up-tray class="w-5 h-5 text-gray-500" />',
                'processing' => '<x-heroicon-o-cog-6-tooth class="w-5 h-5 text-purple-500" />',
                'ready_pickup' => '<x-heroicon-o-inbox-arrow-down class="w-5 h-5 text-teal-500" />',
                'completed' => '<x-heroicon-o-check-badge class="w-5 h-5 text-green-600" />',
                'cancelled' => '<x-heroicon-o-x-mark class="w-5 h-5 text-gray-500" />',
            ];

            return $icons[$status] ?? '<x-heroicon-o-information-circle class="w-5 h-5 text-gray-500" />';
        }
    }

    if (!function_exists('getStatusLabel')) {
        function getStatusLabel($status) {
            $labels = [
                'pengajuan_terkirim' => 'Pengajuan Terkirim',
                'pending_admin_review' => 'Menunggu Review Admin',
                'diajukan_ke_superadmin' => 'Dikirim ke Tim Pengadaan',
                'superadmin_approved' => 'Disetujui Tim Pengadaan',
                'superadmin_rejected' => 'Ditolak Tim Pengadaan',
                'pengajuan_dikirim_ke_direksi' => 'Dikirim ke Direksi',
                'pending_direksi' => 'Pending Direksi',
                'approved_by_direksi' => 'Disetujui Direksi',
                'approved_at_direksi' => 'Tanggal Persetujuan Direksi',
                'reject_direksi' => 'Ditolak Direksi',
                'pengajuan_dikirim_ke_keuangan' => 'Dikirim ke Keuangan',
                'pending_keuangan' => 'Menunggu Keuangan',
                'process_keuangan' => 'Diproses Keuangan',
                'execute_keuangan' => 'Dieksekusi Keuangan',
                'executed_by_keuangan' => 'Dieksekusi oleh Keuangan',
                'executed_at_keuangan' => 'Tanggal Eksekusi Keuangan',
                'pengajuan_dikirim_ke_pengadaan' => 'Dikirim ke Pengadaan',
                'pengajuan_dikirim_ke_admin' => 'Dikirim ke Admin',
                'processing' => 'Sedang Diproses',
                'ready_pickup' => 'Siap Diambil',
                'completed' => 'Selesai',
                'cancelled' => 'Dibatalkan',
            ];

            return $labels[$status] ?? ucfirst(str_replace('_', ' ', $status));
        }
    }

    if (!function_exists('getStatusBadgeColor')) {
        function getStatusBadgeColor($status) {
            $colors = [
                'pengajuan_terkirim' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-200',
                'pending_admin_review' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/50 dark:text-yellow-200',
                'diajukan_ke_superadmin' => 'bg-orange-100 text-orange-800 dark:bg-orange-900/50 dark:text-orange-200',
                'superadmin_approved' => 'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-200',
                'superadmin_rejected' => 'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-200',
                'pengajuan_dikirim_ke_direksi' => 'bg-purple-100 text-purple-800 dark:bg-purple-900/50 dark:text-purple-200',
                'pending_direksi' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/50 dark:text-yellow-200',
                'approved_by_direksi' => 'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-200',
                'approved_at_direksi' => 'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-200',
                'reject_direksi' => 'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-200',
                'pengajuan_dikirim_ke_keuangan' => 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900/50 dark:text-indigo-200',
                'pending_keuangan' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/50 dark:text-yellow-200',
                'process_keuangan' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-200',
                'execute_keuangan' => 'bg-purple-100 text-purple-800 dark:bg-purple-900/50 dark:text-purple-200',
                'executed_by_keuangan' => 'bg-purple-100 text-purple-800 dark:bg-purple-900/50 dark:text-purple-200',
                'executed_at_keuangan' => 'bg-purple-100 text-purple-800 dark:bg-purple-900/50 dark:text-purple-200',
                'pengajuan_dikirim_ke_pengadaan' => 'bg-orange-100 text-orange-800 dark:bg-orange-900/50 dark:text-orange-200',
                'pengajuan_dikirim_ke_admin' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200',
                'processing' => 'bg-purple-100 text-purple-800 dark:bg-purple-900/50 dark:text-purple-200',
                'ready_pickup' => 'bg-teal-100 text-teal-800 dark:bg-teal-900/50 dark:text-teal-200',
                'completed' => 'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-200',
                'cancelled' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200',
            ];

            return $colors[$status] ?? 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200';
        }
    }

    if (!function_exists('getDepartmentInfo')) {
        function getDepartmentInfo($status) {
            $departments = [
                'pengajuan_terkirim' => ['User', 'bg-blue-100 text-blue-800 dark:bg-blue-800/70 dark:text-blue-100'],
                'pending_admin_review' => ['Admin', 'bg-yellow-100 text-yellow-800 dark:bg-yellow-800/70 dark:text-yellow-100'],
                'diajukan_ke_superadmin' => ['Admin', 'bg-orange-100 text-orange-800 dark:bg-orange-800/70 dark:text-orange-100'],
                'superadmin_approved' => ['Pengadaan', 'bg-green-100 text-green-800 dark:bg-green-800/70 dark:text-green-100'],
                'superadmin_rejected' => ['Pengadaan', 'bg-red-100 text-red-800 dark:bg-red-800/70 dark:text-red-100'],
                'pengajuan_dikirim_ke_direksi' => ['Pengadaan', 'bg-purple-100 text-purple-800 dark:bg-purple-800/70 dark:text-purple-100'],
                'pending_direksi' => ['Direksi', 'bg-yellow-100 text-yellow-800 dark:bg-yellow-800/70 dark:text-yellow-100'],
                'approved_by_direksi' => ['Direksi', 'bg-green-100 text-green-800 dark:bg-green-800/70 dark:text-green-100'],
                'approved_at_direksi' => ['Direksi', 'bg-green-100 text-green-800 dark:bg-green-800/70 dark:text-green-100'],
                'reject_direksi' => ['Direksi', 'bg-red-100 text-red-800 dark:bg-red-800/70 dark:text-red-100'],
                'pengajuan_dikirim_ke_keuangan' => ['Direksi', 'bg-indigo-100 text-indigo-800 dark:bg-indigo-800/70 dark:text-indigo-100'],
                'pending_keuangan' => ['Keuangan', 'bg-yellow-100 text-yellow-800 dark:bg-yellow-800/70 dark:text-yellow-100'],
                'process_keuangan' => ['Keuangan', 'bg-blue-100 text-blue-800 dark:bg-blue-800/70 dark:text-blue-100'],
                'execute_keuangan' => ['Keuangan', 'bg-purple-100 text-purple-800 dark:bg-purple-800/70 dark:text-purple-100'],
                'executed_by_keuangan' => ['Keuangan', 'bg-purple-100 text-purple-800 dark:bg-purple-800/70 dark:text-purple-100'],
                'executed_at_keuangan' => ['Keuangan', 'bg-purple-100 text-purple-800 dark:bg-purple-800/70 dark:text-purple-100'],
                'pengajuan_dikirim_ke_pengadaan' => ['Keuangan', 'bg-orange-100 text-orange-800 dark:bg-orange-800/70 dark:text-orange-100'],
                'pengajuan_dikirim_ke_admin' => ['Pengadaan', 'bg-gray-100 text-gray-800 dark:bg-gray-600 dark:text-gray-100'],
                'processing' => ['Admin', 'bg-purple-100 text-purple-800 dark:bg-purple-800/70 dark:text-purple-100'],
                'ready_pickup' => ['Admin', 'bg-teal-100 text-teal-800 dark:bg-teal-800/70 dark:text-teal-100'],
                'completed' => ['Admin', 'bg-green-100 text-green-800 dark:bg-green-800/70 dark:text-green-100'],
                'cancelled' => ['System', 'bg-gray-100 text-gray-800 dark:bg-gray-600 dark:text-gray-100'],
            ];

            return $departments[$status] ?? ['System', 'bg-gray-100 text-gray-800 dark:bg-gray-600 dark:text-gray-100'];
        }
    }

    // Mengatur timezone ke Asia/Jakarta dan locale Indonesia
    \Carbon\Carbon::setLocale('id');
    date_default_timezone_set('Asia/Jakarta');
@endphp

<div class="space-y-4">
    <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4">
        <div class="flex items-center space-x-2 mb-3">
            <x-heroicon-o-information-circle class="w-5 h-5 text-blue-500" />
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                Riwayat Perubahan Status Pengajuan Operasional
            </h3>
        </div>
        <p class="text-sm text-gray-600 dark:text-gray-200">
            Berikut adalah riwayat lengkap perubahan status pengajuan operasional ini.
        </p>
    </div>

    <div class="relative">
        {{-- Timeline line --}}
        <div class="absolute left-6 top-0 bottom-0 w-0.5 bg-gray-200 dark:bg-gray-700"></div>

        @foreach($history as $index => $item)
            <div class="relative flex items-start space-x-4 pb-6 {{ $loop->last ? 'pb-0' : '' }}">
                {{-- Timeline dot --}}
                <div class="relative z-10 flex items-center justify-center">
                    <div class="w-12 h-12 rounded-full flex items-center justify-center {{ $index === 0 ? 'bg-blue-100 dark:bg-blue-900 border-2 border-blue-500' : 'bg-white dark:bg-gray-700 border-2 border-gray-300 dark:border-gray-600' }}">
                        {!! getStatusIcon($item['status']) !!}
                    </div>
                </div>

                {{-- Content --}}
                <div class="flex-1 min-w-0">
                    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-600 p-4 {{ $index === 0 ? 'ring-2 ring-blue-500 ring-opacity-50 dark:ring-opacity-50' : '' }}">
                        {{-- Header --}}
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center space-x-2 flex-wrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ getStatusBadgeColor($item['status']) }}">
                                    {{ getStatusLabel($item['status']) }}
                                </span>
                                @if($index === 0)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-200">
                                        Status Terkini
                                    </span>
                                @endif
                            </div>
                            <time class="text-xs text-gray-700 dark:text-gray-200 whitespace-nowrap ml-2 font-medium">
                                {{ \Carbon\Carbon::parse($item['created_at'])->setTimezone('Asia/Jakarta')->format('d M Y, H:i') }} WIB
                            </time>
                        </div>

                        {{-- Note --}}
                        <p class="text-sm text-gray-800 dark:text-gray-100 mb-3 leading-relaxed font-medium">
                            {{ $item['note'] ?? 'Tidak ada catatan' }}
                        </p>

                        {{-- Additional Info --}}
                        @if (isset($item['additional_info']) && !empty($item['additional_info']))
                            <div class="bg-gray-50 dark:bg-gray-700/70 rounded-lg p-3 mb-3 border border-gray-100 dark:border-gray-600">
                                <h4 class="text-xs font-semibold text-gray-700 dark:text-gray-200 mb-2">Informasi Tambahan:</h4>
                                <p class="text-xs text-gray-800 dark:text-gray-100 leading-relaxed">{{ $item['additional_info'] }}</p>
                            </div>
                        @endif

                        {{-- Rejection reason if exists --}}
                        @if (in_array($item['status'], ['superadmin_rejected', 'reject_direksi']) && isset($item['reject_reason']) && !empty($item['reject_reason']))
                            <div class="bg-red-50 dark:bg-red-900/30 rounded-lg p-3 mb-3 border border-red-200 dark:border-red-800">
                                <div class="flex items-center space-x-2 mb-2">
                                    <x-heroicon-o-exclamation-triangle class="w-4 h-4 text-red-500 dark:text-red-400" />
                                    <h4 class="text-xs font-semibold text-red-700 dark:text-red-200">Alasan Penolakan:</h4>
                                </div>
                                <p class="text-xs text-red-800 dark:text-red-100 leading-relaxed">{{ $item['reject_reason'] }}</p>
                            </div>
                        @endif

                        {{-- Pending date info --}}
                        @if ($item['status'] === 'pending_direksi' && isset($item['tanggal_pending']) && !empty($item['tanggal_pending']))
                            <div class="bg-yellow-50 dark:bg-yellow-900/30 rounded-lg p-3 mb-3 border border-yellow-200 dark:border-yellow-800">
                                <div class="flex items-center space-x-2 mb-2">
                                    <x-heroicon-o-calendar class="w-4 h-4 text-yellow-500 dark:text-yellow-400" />
                                    <h4 class="text-xs font-semibold text-yellow-700 dark:text-yellow-200">Tanggal Pending:</h4>
                                </div>
                                <p class="text-xs text-yellow-800 dark:text-yellow-100 leading-relaxed">
                                    Dipending hingga {{ \Carbon\Carbon::parse($item['tanggal_pending'])->setTimezone('Asia/Jakarta')->format('d M Y') }}
                                </p>
                            </div>
                        @endif

                        {{-- User and Department info --}}
                        <div class="flex items-center justify-between pt-2 border-t border-gray-100 dark:border-gray-700">
                            <div class="flex items-center space-x-2 text-xs text-gray-800 dark:text-gray-100">
                                <x-heroicon-o-user class="w-4 h-4" />
                                <span class="font-semibold">
                                    @php
                                        $user = \App\Models\User::find($item['user_id']);
                                    @endphp
                                    {{ $user ? $user->name : 'Pengguna tidak diketahui' }}
                                </span>
                                <span class="text-gray-500 dark:text-gray-400">•</span>
                                <span class="font-medium">{{ \Carbon\Carbon::parse($item['created_at'])->setTimezone('Asia/Jakarta')->diffForHumans() }}</span>
                            </div>

                            {{-- Department badge --}}
                            <div class="flex items-center space-x-1">
                                @php
                                    $department = getDepartmentInfo($item['status']);
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
