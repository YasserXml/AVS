<div class="p-4 mt-4 bg-white rounded-lg shadow-sm dark:bg-gray-800">
    <div class="flex flex-col lg:flex-row justify-between items-center gap-4">
        <div class="text-center">
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Jenis Barang</p>
            <p class="text-2xl font-bold text-primary-600 dark:text-primary-400">{{ number_format($totalJenis, 0, ',', '.') }}</p>
        </div>
        
        <div class="hidden lg:block h-12 border-l border-gray-200 dark:border-gray-700"></div>
        
        <div class="text-center">
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Stok Barang</p>
            <p class="text-2xl font-bold text-info-600 dark:text-info-400">{{ number_format($totalStok, 0, ',', '.') }}</p>
        </div>
        
        <div class="hidden lg:block h-12 border-l border-gray-200 dark:border-gray-700"></div>
        
        <div class="text-center">
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Nilai Inventaris</p>
            <p class="text-2xl font-bold text-success-600 dark:text-success-400">Rp {{ number_format($totalNilaiInventaris, 0, ',', '.') }}</p>
        </div>
    </div>
    
    <div class="mt-4 text-center text-xs text-gray-500 dark:text-gray-400">
        Rangkuman data inventaris per {{ now()->format('d F Y, H:i') }}
    </div>
</div>