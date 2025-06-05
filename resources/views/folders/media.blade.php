@php
    $currentFolder = \App\Models\Direktoratfolder::find($this->folder_id);
    if(config('media-manager.allow_sub_folders', true)){
        $folders = \App\Models\Direktoratfolder::query()
            ->where('model_type', \App\Models\Direktoratfolder::class)
            ->where('model_id', $this->folder_id)
            ->get();
    }
    else {
        $folders = [];
    }
@endphp

@if(isset($records) || count($folders) > 0)
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 p-4">
    @if(isset($records))
        @foreach($records as $item)
            @if($item instanceof \App\Models\Direktoratfolder)
                {{ ($this->folderAction($item))(['record' => $item]) }}
            @else
                <x-filament::modal width="3xl" slide-over>
                    <x-slot name="trigger" class="w-full h-full">
                        <div class="flex flex-col justify-start gap-4 border dark:border-gray-700 rounded-lg shadow-sm p-2 w-full h-full hover:shadow-md transition-shadow cursor-pointer">
                            <div class="flex flex-col items-center justify-center p-4 h-full">
                                @if(str($item->mime_type)->contains('image'))
                                    <img src="{{ $item->getUrl() }}" alt="{{ $item->name }}" class="max-w-full max-h-32 object-contain rounded" />
                                @elseif(str($item->mime_type)->contains('video'))
                                    <video class="max-w-full max-h-32 object-contain rounded">
                                        <source src="{{ $item->getUrl() }}" type="{{ $item->mime_type }}">
                                        Browser Anda tidak mendukung video.
                                    </video>
                                @elseif(str($item->mime_type)->contains('audio'))
                                    <x-filament::icon icon="heroicon-o-musical-note" class="w-32 h-32 text-gray-400" />
                                @else
                                    @php
                                        $hasPreview = false;
                                        $type = null;
                                        $fileExtension = pathinfo($item->file_name, PATHINFO_EXTENSION);
                                        
                                        // Define file type icons
                                        $fileIcons = [
                                            'pdf' => 'heroicon-o-document-text',
                                            'doc' => 'heroicon-o-document-text',
                                            'docx' => 'heroicon-o-document-text',
                                            'xls' => 'heroicon-o-table-cells',
                                            'xlsx' => 'heroicon-o-table-cells',
                                            'ppt' => 'heroicon-o-presentation-chart-bar',
                                            'pptx' => 'heroicon-o-presentation-chart-bar',
                                            'txt' => 'heroicon-o-document',
                                            'zip' => 'heroicon-o-archive-box',
                                            'rar' => 'heroicon-o-archive-box',
                                        ];
                                        
                                        $icon = $fileIcons[strtolower($fileExtension)] ?? 'heroicon-o-document';
                                    @endphp
                                    <x-filament::icon :icon="$icon" class="w-32 h-32 text-gray-400" />
                                @endif
                            </div>
                            <div>
                                <div class="flex flex-col justify-between border-t dark:border-gray-700 p-4">
                                    <div>
                                        <h1 class="font-bold break-words text-sm">
                                            {{ $item->hasCustomProperty('title') ? (!empty($item->getCustomProperty('title')) ? $item->getCustomProperty('title') : $item->name) : $item->name }}
                                        </h1>
                                    </div>

                                    @if($item->hasCustomProperty('description') && !empty($item->getCustomProperty('description')))
                                        <div class="mt-2">
                                            <div>
                                                <h2 class="font-semibold text-xs text-gray-600 dark:text-gray-400">Deskripsi</h2>
                                            </div>
                                            <div class="flex justify-start">
                                                <p class="text-xs text-gray-500 dark:text-gray-400 line-clamp-2">
                                                    {{ $item->getCustomProperty('description') }}
                                                </p>
                                            </div>
                                        </div>
                                    @endif

                                    <div class="flex justify-start mt-2">
                                        <p class="text-gray-600 dark:text-gray-400 text-xs">
                                            {{ $item->created_at->diffForHumans() }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </x-slot>

                    <x-slot name="heading">
                        {{ $item->name }}
                    </x-slot>

                    <x-slot name="description">
                        {{ $item->file_name }}
                    </x-slot>

                    <div>
                        <div class="flex flex-col justify-start w-full h-full">
                            @if(str($item->mime_type)->contains('image'))
                                <a href="{{ $item->getUrl() }}" target="_blank" class="flex flex-col items-center justify-center p-4 h-full border dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                                    <img src="{{ $item->getUrl() }}" alt="{{ $item->name }}" class="max-w-full max-h-96 object-contain rounded" />
                                </a>

                            @elseif(str($item->mime_type)->contains('video'))
                                <div class="flex flex-col items-center justify-center p-4 h-full border dark:border-gray-700 rounded-lg">
                                    <video class="w-full max-h-96" controls>
                                        <source src="{{ $item->getUrl() }}" type="{{ $item->mime_type }}">
                                        Browser Anda tidak mendukung video.
                                    </video>
                                </div>

                            @elseif(str($item->mime_type)->contains('audio'))
                                <div class="flex flex-col items-center justify-center p-4 h-full border dark:border-gray-700 rounded-lg">
                                    <audio class="w-full" controls>
                                        <source src="{{ $item->getUrl() }}" type="{{ $item->mime_type }}">
                                        Browser Anda tidak mendukung audio.
                                    </audio>
                                </div>
                            @else
                                <a href="{{ $item->getUrl() }}" target="_blank" class="flex flex-col items-center justify-center p-4 h-full border dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                                    @php
                                        $fileExtension = pathinfo($item->file_name, PATHINFO_EXTENSION);
                                        $fileIcons = [
                                            'pdf' => 'heroicon-o-document-text',
                                            'doc' => 'heroicon-o-document-text',
                                            'docx' => 'heroicon-o-document-text',
                                            'xls' => 'heroicon-o-table-cells',
                                            'xlsx' => 'heroicon-o-table-cells',
                                            'ppt' => 'heroicon-o-presentation-chart-bar',
                                            'pptx' => 'heroicon-o-presentation-chart-bar',
                                            'txt' => 'heroicon-o-document',
                                            'zip' => 'heroicon-o-archive-box',
                                            'rar' => 'heroicon-o-archive-box',
                                        ];
                                        $icon = $fileIcons[strtolower($fileExtension)] ?? 'heroicon-o-document';
                                    @endphp
                                    <x-filament::icon :icon="$icon" class="w-32 h-32 text-gray-400 mb-4" />
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Klik untuk mengunduh</p>
                                </a>
                            @endif

                            <div class="flex flex-col gap-4 my-4">
                                @if($item->model)
                                <div>
                                    <div>
                                        <h3 class="font-bold text-sm">Model Terkait</h3>
                                    </div>
                                    <div class="flex justify-start">
                                        <p class="text-sm text-gray-600 dark:text-gray-400">
                                            {{ str($item->model_type)->afterLast('\\')->title() }}[ID: {{ $item->model?->id }}]
                                        </p>
                                    </div>
                                </div>
                                @endif

                                <div>
                                    <div>
                                        <h3 class="font-bold text-sm">Nama File</h3>
                                    </div>
                                    <div class="flex justify-start">
                                        <p class="text-sm text-gray-600 dark:text-gray-400 break-all">
                                            {{ $item->file_name }}
                                        </p>
                                    </div>
                                </div>

                                <div>
                                    <div>
                                        <h3 class="font-bold text-sm">Tipe File</h3>
                                    </div>
                                    <div class="flex justify-start">
                                        <p class="text-sm text-gray-600 dark:text-gray-400">
                                            {{ $item->mime_type }}
                                        </p>
                                    </div>
                                </div>

                                <div>
                                    <div>
                                        <h3 class="font-bold text-sm">Ukuran File</h3>
                                    </div>
                                    <div class="flex justify-start">
                                        <p class="text-sm text-gray-600 dark:text-gray-400">
                                            {{ $item->humanReadableSize }}
                                        </p>
                                    </div>
                                </div>

                                <div>
                                    <div>
                                        <h3 class="font-bold text-sm">Penyimpanan</h3>
                                    </div>
                                    <div class="flex justify-start">
                                        <p class="text-sm text-gray-600 dark:text-gray-400">
                                            {{ $item->disk }}
                                        </p>
                                    </div>
                                </div>

                                <div>
                                    <div>
                                        <h3 class="font-bold text-sm">Tanggal Upload</h3>
                                    </div>
                                    <div class="flex justify-start">
                                        <p class="text-sm text-gray-600 dark:text-gray-400">
                                            {{ $item->created_at->format('d/m/Y H:i') }}
                                        </p>
                                    </div>
                                </div>

                                @if($item->custom_properties)
                                    @foreach($item->custom_properties as $key => $value)
                                        @if($value)
                                            <div>
                                                <div>
                                                    <h3 class="font-bold text-sm">{{ str($key)->title() }}</h3>
                                                </div>
                                                <div class="flex justify-start">
                                                    <p class="text-sm text-gray-600 dark:text-gray-400">
                                                        {{ $value }}
                                                    </p>
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                @endif
                            </div>
                        </div>
                    </div>

                    @if(config('media-manager.allow_user_access', true) && (!empty($currentFolder->user_id)))
                        @if($currentFolder->user_id === filament()->auth()->user()->id && $currentFolder->user_type === get_class(filament()->auth()->user()))
                            <x-slot name="footer">
                                {{ ($this->deleteMedia)(['record' => $item]) }}
                            </x-slot>
                        @endif
                    @else
                        <x-slot name="footer">
                            {{ ($this->deleteMedia)(['record' => $item]) }}
                        </x-slot>
                    @endif
                </x-filament::modal>
            @endif
        @endforeach
    @endif

    {{-- Tampilkan sub-folder jika diizinkan --}}
    @if(config('media-manager.allow_sub_folders', true))
        @foreach($folders as $folder)
            {{ ($this->folderAction($folder))(['record' => $folder]) }}
        @endforeach
    @endif
</div>
@else
    {{-- State kosong --}}
    <div class="fi-ta-empty-state px-6 py-12">
        <div class="fi-ta-empty-state-content mx-auto grid max-w-lg justify-items-center text-center">
            <div class="fi-ta-empty-state-icon-ctn mb-4 rounded-full bg-gray-100 p-3 dark:bg-gray-500/20">
                <x-filament::icon
                    icon="heroicon-o-folder-open"
                    class="fi-ta-empty-state-icon h-6 w-6 text-gray-500 dark:text-gray-400"
                />
            </div>

            <h3 class="fi-ta-empty-state-heading text-base font-semibold text-gray-950 dark:text-white">
                Folder Kosong
            </h3>

            <p class="fi-ta-empty-state-description text-sm text-gray-500 dark:text-gray-400">
                Belum ada file atau folder di lokasi ini.
            </p>
        </div>
    </div>
@endif