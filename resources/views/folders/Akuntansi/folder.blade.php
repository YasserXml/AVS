{{-- resources/views/folders/folder.blade.php --}}
<div class="p-4">
    @php
        // Kelompokkan records berdasarkan kategori
        $groupedRecords = $records->groupBy(function($item) {
            return $item->kategori ? $item->kategori->nama_kategori : 'Tanpa Kategori';
        });
        
        // Urutkan kategori: "Tanpa Kategori" di akhir
        $sortedGroups = $groupedRecords->sortKeys()->when($groupedRecords->has('Tanpa Kategori'), function($collection) {
            $tanpaKategori = $collection->pull('Tanpa Kategori');
            return $collection->put('Tanpa Kategori', $tanpaKategori);
        });
    @endphp

    @foreach($sortedGroups as $kategoriName => $folders)
        <div class="mb-8" x-data="{ expanded: true }">
            {{-- Header Kategori dengan Toggle --}}
            <div class="mb-4">
                <button 
                    @click="expanded = !expanded"
                    class="w-full flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-800 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                >
                    <div class="flex items-center gap-3">
                        <div class="flex items-center gap-2">
                            @if($kategoriName === 'Tanpa Kategori')
                                <x-icon name="heroicon-o-folder" class="w-5 h-5 text-gray-500 dark:text-gray-400"/>
                            @else
                                <x-icon name="heroicon-o-folder" class="w-5 h-5 text-blue-500 dark:text-blue-400"/>
                            @endif
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                                {{ $kategoriName }}
                            </h2>
                        </div>
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium {{ $kategoriName === 'Tanpa Kategori' ? 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300' : 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' }}">
                            {{ $folders->count() }} folder
                        </span>
                    </div>
                    <x-icon 
                        name="heroicon-o-chevron-down" 
                        class="w-5 h-5 text-gray-500 transition-transform duration-200"
                        x-bind:class="{ 'rotate-180': !expanded }"
                    />
                </button>
            </div>

            {{-- Grid Folder dalam Kategori --}}
            <div 
                x-show="expanded" 
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 transform scale-95"
                x-transition:enter-end="opacity-100 transform scale-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 transform scale-100"
                x-transition:leave-end="opacity-0 transform scale-95"
                class="grid grid-cols-1 md:grid-cols-3 xl:grid-cols-4 gap-4"
            >
                @foreach($folders as $item)
                    {{ ($this->folderAction($item))(['record' => $item]) }}
                @endforeach
            </div>
        </div>
    @endforeach

    {{-- Pesan jika tidak ada folder --}}
    @if($records->isEmpty())
        <div class="text-center py-12">
            <div class="mx-auto w-16 h-16 bg-gray-100 dark:bg-gray-800 rounded-full flex items-center justify-center mb-4">
                <x-icon name="heroicon-o-folder" class="w-8 h-8 text-gray-400"/>
            </div>
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">
                Belum Ada Folder
            </h3>
            <p class="text-gray-500 dark:text-gray-400 max-w-sm mx-auto">
                Mulai dengan membuat folder baru untuk mengorganisir file Anda berdasarkan kategori.
            </p>
        </div>
    @endif
</div>

<script>
document.addEventListener('alpine:init', () => {
    // Auto-collapse categories with many items for better UX
    Alpine.data('categoryManager', () => ({
        init() {
            // Bisa ditambahkan logic untuk auto-collapse kategori dengan banyak folder
        }
    }))
})
</script>