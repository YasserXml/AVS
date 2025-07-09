@php
    // Helper methods for status display
    if (!function_exists('getStatusIcon')) {
        function getStatusIcon($status) {
            $icons = [
                'pengajuan_terkirim' => '<x-heroicon-o-paper-airplane class="w-5 h-5 text-blue-500" />',
                'pending_admin_review' => '<x-heroicon-o-eye class="w-5 h-5 text-yellow-500" />',
                'diajukan_ke_superadmin' => '<x-heroicon-o-arrow-up-tray class="w-5 h-5 text-orange-500" />',
                'superadmin_approved' => '<x-heroicon-o-check-circle class="w-5 h-5 text-green-500" />',
                'superadmin_rejected' => '<x-heroicon-o-x-circle class="w-5 h-5 text-red-500" />',
                'processing' => '<x-heroicon-o-cog-6-tooth class="w-5 h-5 text-purple-500" />',
                'ready_pickup' => '<x-heroicon-o-inbox-arrow-down class="w-5 h-5 text-teal-500" />',
                'completed' => '<x-heroicon-o-check-badge class="w-5 h-5 text-green-600" />',
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
                'processing' => 'Sedang Diproses',
                'ready_pickup' => 'Siap Diambil',
                'completed' => 'Selesai',
            ];
            
            return $labels[$status] ?? ucfirst(str_replace('_', ' ', $status));
        }
    }

    if (!function_exists('getStatusBadgeColor')) {
        function getStatusBadgeColor($status) {
            $colors = [
                'pengajuan_terkirim' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
                'pending_admin_review' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
                'diajukan_ke_superadmin' => 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200',
                'superadmin_approved' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
                'superadmin_rejected' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
                'processing' => 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200',
                'ready_pickup' => 'bg-teal-100 text-teal-800 dark:bg-teal-900 dark:text-teal-200',
                'completed' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
            ];
            
            return $colors[$status] ?? 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200';
        }
    }
@endphp
<div class="space-y-4">
    <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4">
        <div class="flex items-center space-x-2 mb-3">
            <x-heroicon-o-information-circle class="w-5 h-5 text-blue-500" />
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                Riwayat Perubahan Status
            </h3>
        </div>
        <p class="text-sm text-gray-600 dark:text-gray-400">
            Berikut adalah riwayat lengkap perubahan status pengajuan ini.
        </p>
    </div>

    <div class="relative">
        {{-- Timeline line --}}
        <div class="absolute left-6 top-0 bottom-0 w-0.5 bg-gray-200 dark:bg-gray-700"></div>
        
        @foreach($history as $index => $item)
            <div class="relative flex items-start space-x-4 pb-6 {{ $loop->last ? 'pb-0' : '' }}">
                {{-- Timeline dot --}}
                <div class="relative z-10 flex items-center justify-center">
                    <div class="w-12 h-12 rounded-full flex items-center justify-center {{ $index === 0 ? 'bg-blue-100 dark:bg-blue-900' : 'bg-gray-100 dark:bg-gray-700' }}">
                        {!! $this->getStatusIcon($item['status']) !!}
                    </div>
                </div>

                {{-- Content --}}
                <div class="flex-1 min-w-0">
                    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4 {{ $index === 0 ? 'ring-2 ring-blue-500 ring-opacity-20' : '' }}">
                        {{-- Header --}}
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center space-x-2">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $this->getStatusBadgeColor($item['status']) }}">
                                    {{ $this->getStatusLabel($item['status']) }}
                                </span>
                                @if($index === 0)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                        Status Terkini
                                    </span>
                                @endif
                            </div>
                            <time class="text-xs text-gray-500 dark:text-gray-400">
                                {{ \Carbon\Carbon::parse($item['created_at'])->format('d M Y, H:i') }}
                            </time>
                        </div>

                        {{-- Note --}}
                        <p class="text-sm text-gray-700 dark:text-gray-300 mb-3">
                            {{ $item['note'] }}
                        </p>

                        {{-- User info --}}
                        <div class="flex items-center space-x-2 text-xs text-gray-500 dark:text-gray-400">
                            <x-heroicon-o-user class="w-4 h-4" />
                            <span>
                                @php
                                    $user = \App\Models\User::find($item['user_id']);
                                @endphp
                                {{ $user ? $user->name : 'Pengguna tidak diketahui' }}
                            </span>
                            <span>•</span>
                            <span>{{ \Carbon\Carbon::parse($item['created_at'])->diffForHumans() }}</span>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>