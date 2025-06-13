<a href="{{ $url ?? '#' }}" 
   class="flex flex-col justify-start gap-4 border dark:border-gray-700 rounded-lg shadow-sm p-4 w-full h-full hover:shadow-md transition-shadow cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800">
    
    <div class="flex flex-col items-center justify-center p-4 h-full">
        <x-filament::icon icon="heroicon-o-folder" class="w-32 h-32 text-blue-500" />
    </div>
    
    <div class="flex flex-col justify-between border-t dark:border-gray-700 p-4">
        <div>
            <h1 class="font-bold break-words text-sm">
                {{ $folder->name }}
            </h1>
        </div>

        @if ($folder->description)
            <div class="mt-2">
                <div>
                    <h2 class="font-semibold text-xs text-gray-600 dark:text-gray-400">
                        Deskripsi
                    </h2>
                </div>
                <div class="flex justify-start">
                    <p class="text-xs text-gray-500 dark:text-gray-400 line-clamp-2">
                        {{ $folder->description }}
                    </p>
                </div>
            </div>
        @endif

        <div class="flex justify-start mt-2">
            <p class="text-gray-600 dark:text-gray-400 text-xs">
                {{ $folder->created_at->diffForHumans() }}
            </p>
        </div>
    </div>
</a>