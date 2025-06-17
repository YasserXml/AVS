@php
    $currentFolder = $this->folder; // Dari ListDirektoratmedia
    if (config('media-manager.allow_sub_folders', true)) {
        $folders = $this->subfolders; // Dari ListDirektoratmedia
    } else {
        $folders = [];
    }
@endphp

@if (isset($records) || count($folders) > 0)
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 p-4">
        {{-- Tampilkan sub-folder dengan style konsisten --}}
        @if (config('media-manager.allow_sub_folders', true))
            @foreach ($folders as $folder)
                <a href="{{ \App\Filament\Resources\DirektoratmediaResource::getUrlFromFolder($folder) }}"
                    class="flex flex-col justify-center items-center gap-4 border dark:border-gray-700 rounded-lg shadow-sm p-6 w-full h-full hover:shadow-md transition-shadow cursor-pointer bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-750">

                    {{-- Folder Icon Section --}}
                    <div class="flex flex-col items-center justify-center">
                        <style>
                            .folder-icon-{{ $folder->id }} {
                                width: 80px;
                                height: 55px;
                                background-color: {{ $folder->color ?? '#f3c623' }};
                                border-radius: 4px;
                                position: relative;
                                margin-bottom: 8px;
                                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
                                display: flex;
                                align-items: center;
                                justify-content: center;
                            }

                            .folder-icon-{{ $folder->id }}::before {
                                content: "";
                                width: 30px;
                                height: 8px;
                                background-color: {{ $folder->color ?? '#f3c623' }};
                                border-radius: 4px 4px 0 0;
                                position: absolute;
                                top: -8px;
                                left: 8px;
                                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
                            }
                        </style>

                        <div class="folder-icon-{{ $folder->id }}">
                            @if ($folder->icon)
                                <x-filament::icon :icon="$folder->icon" class="text-white w-5 h-5" />
                            @endif
                        </div>

                        @if ($folder->is_protected)
                            <div class="flex items-center gap-1 text-xs text-gray-500 dark:text-gray-400 mt-1">
                                <x-filament::icon icon="heroicon-o-lock-closed" class="w-3 h-3" />
                                <span>Terproteksi</span>
                            </div>
                        @endif
                    </div>

                    {{-- Folder Info Section --}}
                    <div class="flex flex-col items-center justify-center text-center w-full">
                        <h1 class="font-semibold text-base text-gray-900 dark:text-white mb-2 line-clamp-1">
                            {{ $folder->name }}
                        </h1>

                        @if ($folder->description)
                            <p class="text-xs text-gray-600 dark:text-gray-400 mb-2 line-clamp-2">
                                {{ $folder->description }}
                            </p>
                        @endif

                        <div class="flex flex-col items-center gap-1 text-xs text-gray-500 dark:text-gray-400">
                            <span>{{ $folder->subfolders()->count() }} folder, {{ $folder->direktoratmedia()->count() }}
                                file</span>
                            <span>{{ $folder->created_at->diffForHumans() }}</span>
                        </div>
                    </div>
                </a>
            @endforeach
        @endif

        {{-- Tampilkan file media --}}
        @if (isset($records))
            @foreach ($records as $item)
                @if (!($item instanceof \App\Models\Direktoratfolder))
                    <x-filament::modal width="lg">
                        <x-slot name="trigger">
                            <div
                                class="flex flex-col justify-center items-center gap-4 border dark:border-gray-700 rounded-lg shadow-sm p-6 w-full h-full hover:shadow-md transition-shadow cursor-pointer bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-750">

                                {{-- File Preview Section --}}
                                <div class="flex flex-col items-center justify-center">
                                    @if (str($item->mime_type)->contains('image'))
                                        <img src="{{ $item->url }}" alt="{{ $item->name }}"
                                            class="w-20 h-20 object-cover rounded border shadow-sm" />
                                    @elseif(str($item->mime_type)->contains('video'))
                                        <div
                                            class="relative w-20 h-20 bg-gray-100 dark:bg-gray-700 rounded border shadow-sm flex items-center justify-center">
                                            <x-filament::icon icon="heroicon-o-play-circle"
                                                class="w-8 h-8 text-gray-400" />
                                        </div>
                                    @elseif(str($item->mime_type)->contains('audio'))
                                        <div
                                            class="w-20 h-20 flex items-center justify-center bg-gray-100 dark:bg-gray-700 rounded border shadow-sm">
                                            <x-filament::icon icon="heroicon-o-musical-note"
                                                class="w-8 h-8 text-gray-400" />
                                        </div>
                                    @else
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
                                        <div
                                            class="w-20 h-20 flex items-center justify-center bg-gray-100 dark:bg-gray-700 rounded border shadow-sm">
                                            <x-filament::icon :icon="$icon" class="w-8 h-8 text-gray-400" />
                                        </div>
                                    @endif
                                </div>

                                {{-- File Info Section --}}
                                <div class="flex flex-col items-center justify-center text-center w-full">
                                    <h1 class="font-semibold text-base text-gray-900 dark:text-white mb-2 line-clamp-2">
                                        {{ $item->hasCustomProperty('title') ? (!empty($item->getCustomProperty('title')) ? $item->getCustomProperty('title') : $item->name) : $item->name }}
                                    </h1>

                                    @if ($item->hasCustomProperty('description') && !empty($item->getCustomProperty('description')))
                                        <p class="text-xs text-gray-600 dark:text-gray-400 mb-2 line-clamp-2">
                                            {{ $item->getCustomProperty('description') }}
                                        </p>
                                    @endif

                                    <div
                                        class="flex flex-col items-center gap-1 text-xs text-gray-500 dark:text-gray-400">
                                        <span>{{ $item->humanReadableSize }}</span>
                                        <span>{{ $item->created_at->diffForHumans() }}</span>
                                    </div>
                                </div>
                            </div>
                        </x-slot>

                        <x-slot name="heading">
                            <div class="text-lg font-semibold">
                                {{ $item->hasCustomProperty('title') ? (!empty($item->getCustomProperty('title')) ? $item->getCustomProperty('title') : $item->name) : $item->name }}
                            </div>
                        </x-slot>

                        <x-slot name="description">
                            <div class="text-sm text-gray-600 dark:text-gray-400">
                                {{ $item->file_name }}
                            </div>
                        </x-slot>

                        {{-- Modal Content --}}
                        <div class="space-y-4">
                            {{-- Preview Section --}}
                            <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4">
                                @if (str($item->mime_type)->contains('image'))
                                    <a href="{{ $item->url }}" target="_blank" class="block">
                                        <img src="{{ $item->url }}" alt="{{ $item->name }}"
                                            class="max-w-full max-h-64 object-contain rounded mx-auto" />
                                    </a>
                                @elseif(str($item->mime_type)->contains('video'))
                                    <video class="w-full max-h-64 rounded" controls>
                                        <source src="{{ $item->url }}" type="{{ $item->mime_type }}">
                                        Browser Anda tidak mendukung video.
                                    </video>
                                @elseif(str($item->mime_type)->contains('audio'))
                                    <div class="flex flex-col items-center space-y-3">
                                        <x-filament::icon icon="heroicon-o-musical-note"
                                            class="w-12 h-12 text-gray-400" />
                                        <audio class="w-full" controls>
                                            <source src="{{ $item->url }}" type="{{ $item->mime_type }}">
                                            Browser Anda tidak mendukung audio.
                                        </audio>
                                    </div>
                                @else
                                    <a href="{{ $item->url }}" download="{{ $item->file_name }}"
                                        class="flex flex-col items-center justify-center p-6 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors rounded">
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
                                        <x-filament::icon :icon="$icon" class="w-16 h-16 text-gray-400 mb-3" />
                                        <p class="text-sm text-gray-600 dark:text-gray-400 font-medium">Klik untuk
                                            mengunduh</p>
                                    </a>
                                @endif
                            </div>

                            {{-- File Information --}}
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                <div class="space-y-3">
                                    <div>
                                        <h3 class="font-semibold text-sm text-gray-900 dark:text-white mb-1">Nama File
                                        </h3>
                                        <p
                                            class="text-sm text-gray-600 dark:text-gray-400 break-all bg-gray-50 dark:bg-gray-800 p-2 rounded text-xs">
                                            {{ $item->file_name }}
                                        </p>
                                    </div>

                                    <div>
                                        <h3 class="font-semibold text-sm text-gray-900 dark:text-white mb-1">Tipe File
                                        </h3>
                                        <p
                                            class="text-sm text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-800 p-2 rounded text-xs">
                                            {{ $item->mime_type }}
                                        </p>
                                    </div>

                                    <div>
                                        <h3 class="font-semibold text-sm text-gray-900 dark:text-white mb-1">Ukuran File
                                        </h3>
                                        <p
                                            class="text-sm text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-800 p-2 rounded text-xs">
                                            {{ $item->humanReadableSize }}
                                        </p>
                                    </div>
                                </div>

                                <div class="space-y-3">
                                    <div>
                                        <h3 class="font-semibold text-sm text-gray-900 dark:text-white mb-1">Tanggal
                                            Upload</h3>
                                        <p
                                            class="text-sm text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-800 p-2 rounded text-xs">
                                            {{ $item->created_at->format('d/m/Y H:i') }}
                                        </p>
                                    </div>

                                    @if ($item->custom_properties)
                                        @foreach ($item->custom_properties as $key => $value)
                                            @if ($value)
                                                <div>
                                                    <h3
                                                        class="font-semibold text-sm text-gray-900 dark:text-white mb-1">
                                                        {{ str($key)->title() }}</h3>
                                                    <p
                                                        class="text-sm text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-800 p-2 rounded text-xs">
                                                        {{ $value }}
                                                    </p>
                                                </div>
                                            @endif
                                        @endforeach
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Modal Footer dengan tombol hapus dan tutup --}}
                        <x-slot name="footer">
                            <div class="flex justify-between w-full">
                                {{-- Tombol Hapus di kiri --}}
                                <div>
                                    @if (config('media-manager.allow_user_access', true) && !empty($currentFolder->user_id))
                                        @if (
                                            $currentFolder->user_id === filament()->auth()->user()->id &&
                                                $currentFolder->user_type === get_class(filament()->auth()->user()))
                                            {{ ($this->deleteMedia)(['record' => $item]) }}
                                        @endif
                                    @else
                                        {{ ($this->deleteMedia)(['record' => $item]) }}
                                    @endif
                                </div>
                            </div>
                        </x-slot>
                    </x-filament::modal>
                @endif
            @endforeach
        @endif
    </div>
@else
    {{-- State kosong --}}
    <div class="fi-ta-empty-state px-6 py-12">
        <div class="fi-ta-empty-state-content mx-auto grid max-w-lg justify-items-center text-center">
            <div class="fi-ta-empty-state-icon-ctn mb-4 rounded-full bg-gray-100 p-3 dark:bg-gray-500/20">
                <x-filament::icon icon="heroicon-o-folder-open"
                    class="fi-ta-empty-state-icon h-6 w-6 text-gray-500 dark:text-gray-400" />
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
