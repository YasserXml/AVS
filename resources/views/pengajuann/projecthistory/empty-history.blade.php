{{-- File: resources/views/pengajuann/history/project-empty-status.blade.php --}}

<div class="flex flex-col items-center justify-center py-12 px-4">
    <div class="bg-gray-100 dark:bg-gray-800 rounded-full p-4 mb-4">
        <x-heroicon-o-clock class="w-12 h-12 text-gray-400" />
    </div>
    
    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
        Tidak Ada Riwayat Status
    </h3>
    
    <p class="text-sm text-gray-600 dark:text-gray-400 text-center max-w-md">
        Pengajuan project ini belum memiliki riwayat perubahan status. Riwayat akan muncul ketika status pengajuan diperbarui.
    </p>
    
    <div class="mt-6 bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4 max-w-md">
        <div class="flex items-center space-x-2 mb-2">
            <x-heroicon-o-information-circle class="w-5 h-5 text-blue-500" />
            <span class="text-sm font-medium text-blue-900 dark:text-blue-100">
                Informasi
            </span>
        </div>
        <p class="text-xs text-blue-700 dark:text-blue-200">
            Riwayat status akan mencakup semua perubahan mulai dari pengajuan terkirim hingga selesai, 
            termasuk proses review PM, pengadaan, direksi, dan keuangan.
        </p>
    </div>
</div>