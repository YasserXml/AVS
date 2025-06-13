<?php

namespace App\Filament\Resources\DirektoratmediaResource\Pages;

use App\Filament\Resources\DirektoratmediaResource;
use App\Models\Direktoratfolder;
use App\Models\Direktoratmedia;
use Closure;
use Filament\Actions;
use Filament\Forms\Components\ColorPicker;
use Illuminate\Support\Str;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class ListDirektoratmedia extends ListRecords
{
    protected static string $resource = DirektoratmediaResource::class;

    public ?int $folder_id = null;
    public ?Direktoratfolder $folder = null;
    public $subfolders = null; //Property untuk menyimpan subfolder

   public function getTitle(): string|Htmlable
    {
        return $this->folder?->full_name_path ?? 'Media Direktorat';
    }

    public function mount(): void
    {
        parent::mount();

        // Ambil folder berdasarkan slug
        $folderSlug = request()->get('folder');

        if (!$folderSlug) {
            abort(404, 'Slug folder diperlukan');
        }

        $folder = Direktoratfolder::where('slug', $folderSlug)->first();

        if (!$folder) {
            abort(404, 'Folder tidak ditemukan');
        }

        // Cek proteksi folder
        if ($folder->is_protected && !session()->has('folder_password_' . $folder->id)) {
            abort(403, 'Anda tidak dapat mengakses folder ini');
        }

        $this->folder = $folder;
        $this->slug = $folderSlug;

        // Set session untuk backward compatibility
        session()->put('folder_id', $this->folder->id);
        session()->put('folder_slug', $this->folder_slug);

        // Load subfolders
        $this->loadSubfolders();
    }

    // TAMBAHAN: Method untuk load subfolders
    protected function loadSubfolders()
    {
        $this->subfolders = $this->getSubfoldersQuery()->get();
    }

    protected function getHeaderActions(): array
    {
        $actions = [
            $this->createMediaAction(),
            $this->createSubFolderAction(),
            $this->editCurrentFolderAction(),
            $this->deleteFolderAction(),
        ];

        //  Tombol kembali ke parent folder
        if ($this->folder && $this->folder->parent_id) {
            array_unshift($actions, $this->backToParentAction());
        } else {
            // Jika ini root folder, tombol kembali ke daftar folder utama
            array_unshift($actions, $this->backToMainAction());
        }

        return $actions;
    }

    // TAMBAHAN: Action untuk kembali ke parent folder
    protected function backToParentAction()
    {
        return Actions\Action::make('backToParent')
            ->label('Kembali')
            ->icon('heroicon-o-arrow-left')
            ->color('gray')
            ->url(function () {
                if ($this->folder->parent_id) {
                    return route('filament.admin.resources.arsip.direktorat.folder.index', [
                        'folder_id' => $this->folder->parent_id
                    ]);
                }
                return route('filament.admin.resources.arsip.direktorat.index');
            });
    }

    protected function getRedirectUrl(): string
    {
        // Redirect kembali ke halaman list setelah membuat folder
        return $this->getResource()::getUrl('index');
    }

    // TAMBAHAN: Action untuk kembali ke halaman utama
    protected function backToMainAction()
    {
        return Actions\Action::make('backToMain')
            ->label('Kembali')
            ->icon('heroicon-o-arrow-left')
            ->color('gray')
            ->url(route('filament.admin.resources.arsip.direktorat.index'));
    }

    protected function getTableQuery(): Builder
    {
        // Hanya tampilkan media yang ada di folder ini
        return Direktoratmedia::query()
            ->where('model_type', Direktoratfolder::class)
            ->where('model_id', $this->folder_id);
    }

    protected function getSubfoldersQuery(): Builder
    {
        // Query untuk subfolder
        return Direktoratfolder::query()
            ->where('parent_id', $this->folder_id)
            ->orderBy('name');
    }

    public function deleteMedia()
    {
        return Actions\Action::make('deleteMedia')
            ->label('Hapus Media')
            ->icon('heroicon-o-trash')
            ->color('danger')
            ->requiresConfirmation()
            ->action(function (array $arguments) {
                // Hapus media  
                $media = Direktoratmedia::find($arguments['record']['id']);
                $media->delete();

                Notification::make()
                    ->title('Media berhasil dihapus')
                    ->success()
                    ->send();

                // Refresh halaman
                return redirect()->route('filament.admin.resources.arsip.direktorat.folder.index', [
                    'folder_id' => $this->folder_id
                ]);
            });
    }

    // Method folderAction untuk membuka subfolder
    public function folderAction($folder)
    {
        return Actions\Action::make('openFolder_' . $folder->id)
            ->label($folder->name)
            ->icon($folder->getDefaultIcon())
            ->color('primary')
            ->button()
            ->action(function () use ($folder) {
                // Cek apakah folder terproteksi
                if ($folder->is_protected) {
                    // Jika belum ada session password untuk folder ini
                    if (!session()->has('folder_password_' . $folder->id)) {
                        $this->dispatch('open-modal', id: 'folder-password-' . $folder->id);
                        return;
                    }
                }

                // Redirect ke folder
                return redirect()->route('filament.admin.resources.arsip.direktorat.folder.index', [
                    'folder_id' => $folder->id
                ]);
            })
            ->extraAttributes([
                'class' => 'w-full justify-start'
            ]);
    }

    public function createMediaAction()
    {
        return Actions\Action::make('createMedia')
            ->label('Upload Media')
            ->icon('heroicon-o-plus')
            ->color('success')
            ->form([
                FileUpload::make('files')
                    ->label('File')
                    ->multiple()
                    ->required()
                    ->preserveFilenames(true)
                    ->disk('public')
                    ->directory('media'),
                TextInput::make('title')
                    ->label('Judul')
                    ->maxLength(255),
                Textarea::make('description')
                    ->label('Deskripsi')
                    ->maxLength(500),
            ])
            ->action(function (array $data) {
                $folder_id = $this->folder_id;
                $folder = Direktoratfolder::find($folder_id);

                foreach ($data['files'] as $file) {
                    $media = new Direktoratmedia();

                    // Relasi polymorphic ke folder yang benar
                    $media->model_type = Direktoratfolder::class;
                    $media->model_id = $folder_id;
                    $media->uuid = Str::uuid();
                    $media->collection_name = $folder->collection ?? 'default';
                    $media->name = $data['title'] ?? pathinfo($file, PATHINFO_FILENAME);
                    $media->file_name = $file;

                    $filePath = storage_path('app/public/' . $file);
                    $media->mime_type = mime_content_type($filePath);
                    $media->disk = 'public';
                    $media->size = filesize($filePath);
                    $media->manipulations = [];
                    $media->custom_properties = [
                        'title' => $data['title'] ?? null,
                        'description' => $data['description'] ?? null,
                    ];
                    $media->generated_conversions = [];
                    $media->responsive_images = [];
                    $media->save();
                }

                Notification::make()
                    ->title('Media berhasil diupload')
                    ->success()
                    ->send();

                // Refresh halaman
                return redirect()->route('filament.admin.resources.arsip.direktorat.folder.index', [
                    'folder_id' => $folder_id
                ]);
            });
    }

    public function createSubFolderAction()
    {
        return Actions\Action::make('createSubFolder')
            ->label('Buat Sub Folder')
            ->icon('heroicon-o-folder-plus')
            ->color('primary')
            ->form([
                TextInput::make('name')
                    ->label('Nama Folder')
                    ->required()
                    ->maxLength(255),
                Textarea::make('description')
                    ->label('Deskripsi')
                    ->maxLength(255),
                ColorPicker::make('color')
                    ->label('Warna Folder')
                    ->default('#ffab09'),
            ])
            ->action(function (array $data) {
                $folder_id = $this->folder_id;
                $parentFolder = Direktoratfolder::find($folder_id);

                if (!$parentFolder) {
                    Notification::make()
                        ->title('Folder utama tidak ditemukan')
                        ->danger()
                        ->send();
                    return;
                }
                try {
                    // Buat subfolder baru
                    $folder = new Direktoratfolder();
                    $folder->name = $data['name'];
                    $folder->description = $data['description'] ?? null;
                    $folder->color = $data['color'] ?? '#ffab09';
                    $folder->icon = 'heroicon-o-folder';
                    // KUNCI UTAMA: Set parent_id untuk menandai ini adalah subfolder
                    $folder->parent_id = $folder_id;
                    // PENTING: Untuk subfolder, kosongkan field ini agar tidak muncul di halaman utama
                    $folder->collection = null;
                    $folder->model_type = null;
                    $folder->model_id = null;
                    // Set user info
                    $folder->user_id = filament()->auth()->id();
                    $folder->user_type = get_class(filament()->auth()->user());
                    // Set proteksi password jika ada
                    $folder->is_protected = $data['is_protected'] ?? false;
                    if ($folder->is_protected && !empty($data['password'])) {
                        $folder->password = $data['password'];
                    }
                    // Set default values
                    $folder->is_hidden = false;
                    $folder->is_public = false;
                    $folder->has_user_access = false;

                    $folder->save();

                    Notification::make()
                        ->title('Sub folder "' . $data['name'] . '" berhasil dibuat')
                        ->success()
                        ->send();
                    // Refresh data subfolder
                    $this->loadSubfolders();

                    // Refresh tabel
                    $this->dispatch('refresh-table');

                    // Redirect untuk memastikan data ter-refresh
                    return redirect()->route('filament.admin.resources.arsip.direktorat.folder.index', [
                        'folder_id' => $folder_id
                    ]);
                } catch (\Exception $e) {
                    Notification::make()
                        ->title('Gagal membuat subfolder')
                        ->body('Error: ' . $e->getMessage())
                        ->danger()
                        ->send();
                }
            });
    }

    public function deleteFolderAction()
    {
        return Actions\Action::make('deleteFolder')
            ->label('Hapus Folder')
            ->icon('heroicon-o-trash')
            ->color('danger')
            ->requiresConfirmation()
            ->modalHeading('Hapus Folder')
            ->modalDescription('Apakah Anda yakin ingin menghapus folder ini? Semua media dan subfolder di dalamnya akan ikut terhapus.')
            ->action(function () {
                $folder_id = $this->folder_id;
                $folder = Direktoratfolder::find($folder_id);

                if ($folder) {
                    $parentId = $folder->parent_id;

                    // Gunakan method deleteRecursively
                    $folder->deleteRecursively();

                    Notification::make()
                        ->title('Folder berhasil dihapus')
                        ->success()
                        ->send();

                    // Redirect ke parent folder atau halaman utama
                    if ($parentId) {
                        return redirect()->route('filament.admin.resources.arsip.direktorat.folder.index', [
                            'folder_id' => $parentId
                        ]);
                    } else {
                        return redirect()->route('filament.admin.resources.arsip.direktorat.index');
                    }
                }
            });
    }

    public function editCurrentFolderAction()
    {
        return Actions\Action::make('editCurrentFolder')
            ->label('Edit Folder')
            ->icon('heroicon-o-pencil')
            ->color('warning')
            ->form([
                TextInput::make('name')
                    ->label('Nama')
                    ->required()
                    ->maxLength(255)
                    ->default(fn() => $this->folder->name),
                Textarea::make('description')
                    ->label('Deskripsi')
                    ->maxLength(255)
                    ->default(fn() => $this->folder->description),
                ColorPicker::make('color')
                    ->label('Warna Folder')
                    ->default(fn() => $this->folder->color ?? '#10b981'),
            ])
            ->action(function (array $data) {
                $folder_id = $this->folder_id;
                $folder = Direktoratfolder::find($folder_id);

                // Update password hanya jika folder terproteksi
                if ($data['is_protected']) {
                    $data['password'] = $data['password'];
                } else {
                    $data['password'] = null;
                }

                $folder->update($data);

                Notification::make()
                    ->title('Folder berhasil diubah')
                    ->success()
                    ->send();

                // Refresh folder data
                $this->folder = $folder->fresh();
            });
    }
}
