<?php

namespace App\Filament\Resources\Divisi3dmediaResource\Pages;

use App\Filament\Resources\Divisi3dmediaResource;
use App\Models\Divisi3dfolder;
use App\Models\Divisi3dmedia;
use Filament\Actions;
use Illuminate\Support\Str;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class ListDivisi3dmedia extends ListRecords
{
    protected static string $resource = Divisi3dmediaResource::class;

    public ?string $folderSlug = null;
    public ?int $folder_id = null;
    public ?Divisi3dfolder $folder = null;
    public $subfolders = null;

    public function getTitle(): string|Htmlable
    {
        return $this->folder?->full_name_path ?? 'Media 3D';
    }

    public function mount(): void
    {
        parent::mount();

        // Ambil folder berdasarkan slug
        $folderSlug = request()->get('folder');

        if (!$folderSlug) {
            abort(404, 'Slug folder diperlukan');
        }

        $folder = Divisi3dfolder::where('slug', $folderSlug)->first();

        if (!$folder) {
            abort(404, 'Folder tidak ditemukan');
        }

        // Cek proteksi folder
        if ($folder->is_protected && !session()->has('folder_password_' . $folder->id)) {
            abort(403, 'Anda tidak dapat mengakses folder ini');
        }

        $this->folder = $folder;
        $this->folder_id = $folder->id; // Set folder_id untuk kompatibilitas
        $this->folderSlug = $folderSlug; // Gunakan folderSlug instead of slug

        // Set session untuk backward compatibility
        session()->put('folder_id', $this->folder->id);
        session()->put('folder_slug', $folderSlug);

        // Load subfolders
        $this->loadSubfolders();
    }

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

        // Tombol kembali
        if ($this->folder && $this->folder->parent_id) {
            array_unshift($actions, $this->backToParentAction());
        } else {
            array_unshift($actions, $this->backToMainAction());
        }

        return $actions;
    }

    protected function backToParentAction()
    {
        return Actions\Action::make('backToParent')
            ->label('Kembali')
            ->icon('heroicon-o-arrow-left')
            ->color('gray')
            ->url(function () {
                if ($this->folder->parent_id) {
                    $parentFolder = Divisi3dfolder::find($this->folder->parent_id);
                    if ($parentFolder) {
                        return route('filament.admin.resources.arsip.3d.folder.index', [
                            'folder' => $parentFolder->slug // Gunakan slug, bukan folder_id
                        ]);
                    }
                }
                return route('filament.admin.resources.arsip.3d.index');
            });
    }

    protected function backToMainAction()
    {
        return Actions\Action::make('backToMain')
            ->label('Kembali ke Halaman Utama')
            ->icon('heroicon-o-arrow-left')
            ->color('gray')
            ->url(route('filament.admin.resources.arsip.3d.index'));
    }

    protected function getTableQuery(): Builder
    {
        return Divisi3dmedia::query()
            ->where('model_type', Divisi3dfolder::class)
            ->where('model_id', $this->folder_id)
            ->where('user_id', filament()->auth()->id())

            ->orderBy('created_at', 'desc');
    }

    protected function getSubfoldersQuery(): Builder
    {
        return Divisi3dfolder::query()
            ->where('parent_id', $this->folder_id)
            ->where(function ($query) {
                // Tampilkan folder milik user atau folder public
                $query->where('user_id', filament()->auth()->id())
                      ->orWhere('is_public', true);
            })

            ->orderBy('name');
    }

    public function getBreadcrumbs(): array
    {
        $breadcrumbs = [];

        // Tambahkan breadcrumb untuk folder parent
        if ($this->folder) {
            $folders = [];
            $currentFolder = $this->folder;

            // Kumpulkan semua parent folder
            while ($currentFolder) {
                array_unshift($folders, $currentFolder);
                $currentFolder = $currentFolder->parent;
            }

            // Buat breadcrumb untuk setiap folder
            foreach ($folders as $folder) {
                $breadcrumbs[$folder->name] = Divisi3dmediaResource::getUrlFromFolder3D($folder);
            }
        }

        return $breadcrumbs;
    }


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
                    if (!session()->has('folder_password_' . $folder->id)) {
                        $this->dispatch('open-modal', id: 'folder-password-' . $folder->id);
                        return;
                    }
                }

                // Redirect menggunakan slug
                return redirect()->route('filament.admin.resources.arsip.3d.folder.index', [
                    'folder' => $folder->slug // Gunakan slug, bukan folder_id
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
                    ->disk('3d_media')
                    ->directory('3d'),
                TextInput::make('title')
                    ->label('Judul')
                    ->maxLength(255),
                Textarea::make('description')
                    ->label('Deskripsi')
                    ->maxLength(500),
            ])
            ->action(function (array $data) {
                $folder_id = $this->folder_id;
                $folder = Divisi3dfolder::find($folder_id);

                foreach ($data['files'] as $file) {
                    $media = new Divisi3dmedia();
                    $media->model_type = Divisi3dfolder::class;
                    $media->model_id = $folder_id;
                    $media->uuid = Str::uuid();
                    $media->collection_name = $folder->collection ?? 'default';
                    $media->name = $data['title'] ?? pathinfo($file, PATHINFO_FILENAME);
                    $media->file_name = $file;

                    $filePath = storage_path('app/public/3d/' . $file);
                    $media->mime_type = mime_content_type($filePath);
                    $media->disk = '3d_media';
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

                // FIX: Refresh data dan tetap di halaman yang sama
                $this->loadSubfolders();
                $this->dispatch('$refresh');
            });
    }

    public function deleteMedia()
    {
        return Actions\Action::make('deleteMedia')
            ->label('Hapus Media')
            ->icon('heroicon-o-trash')
            ->color('danger')
            ->requiresConfirmation()
            ->modalHeading('Konfirmasi Hapus')
            ->modalDescription('Apakah Anda yakin ingin menghapus media ini?')
            ->action(function (array $arguments) {
                try {
                    // Pastikan record ada
                    if (!isset($arguments['record']) || !isset($arguments['record']['id'])) {
                        throw new \Exception('Record tidak ditemukan');
                    }

                    $media = Divisi3dmedia::find($arguments['record']['id']);

                    if (!$media) {
                        throw new \Exception('Media tidak ditemukan');
                    }

                    // Hapus file fisik terlebih dahulu
                    if ($media->exists()) {
                        $media->deleteFile();
                    }

                    // Kemudian hapus record dari database
                    $media->delete();

                    Notification::make()
                        ->title('Media berhasil dihapus')
                        ->success()
                        ->send();

                    // FIX: Refresh data tanpa redirect
                    return redirect()->route('filament.admin.resources.arsip.3d.folder.index', [
                        'folder' => $this->folder->slug
                    ]);
                } catch (\Exception $e) {
                    Notification::make()
                        ->title('Gagal menghapus media')
                        ->body($e->getMessage())
                        ->danger()
                        ->send();
                }
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
                try {
                    $folder = new Divisi3dfolder();
                    $folder->name = $data['name'];
                    $folder->description = $data['description'] ?? null;
                    $folder->color = $data['color'] ?? '#ffab09';
                    $folder->icon = 'heroicon-o-folder';
                    $folder->parent_id = $this->folder_id;
                    $folder->collection = null;
                    $folder->model_type = null;
                    $folder->model_id = null;
                    $folder->user_id = filament()->auth()->id();
                    $folder->user_type = get_class(filament()->auth()->user());
                    $folder->is_protected = false;
                    $folder->is_hidden = false;
                    $folder->is_public = false;
                    $folder->has_user_access = false;

                    $folder->save();

                    Notification::make()
                        ->title('Sub folder "' . $data['name'] . '" berhasil dibuat')
                        ->success()
                        ->send();

                    $this->loadSubfolders();

                    // Redirect menggunakan slug parent
                    return redirect()->route('filament.admin.resources.arsip.3d.folder.index', [
                        'folder' => $this->folder->slug
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
                try {
                    $folder = $this->folder;
                    $parentFolder = $folder->parent;

                    // Hapus semua media dalam folder terlebih dahulu
                    $medias = Divisi3dmedia::where('model_type', Divisi3dfolder::class)
                        ->where('model_id', $folder->id)
                        ->get();

                    foreach ($medias as $media) {
                        if ($media->exists()) {
                            $media->deleteFile();
                        }
                        $media->delete();
                    }

                    // Kemudian hapus folder
                    $folder->delete();

                    Notification::make()
                        ->title('Folder berhasil dihapus')
                        ->success()
                        ->send();

                    // Redirect ke parent atau halaman utama
                    if ($parentFolder) {
                        return redirect()->route('filament.admin.resources.arsip.3d.folder.index', [
                            'folder' => $parentFolder->slug
                        ]);
                    } else {
                        return redirect()->route('filament.admin.resources.arsip.3d.index');
                    }
                } catch (\Exception $e) {
                    Notification::make()
                        ->title('Gagal menghapus folder')
                        ->body($e->getMessage())
                        ->danger()
                        ->send();
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
                $this->folder->update($data);

                Notification::make()
                    ->title('Folder berhasil diubah')
                    ->success()
                    ->send();

                // Refresh folder data
                $this->folder = $this->folder->fresh();

                // Jika nama berubah, slug mungkin berubah, redirect ke slug baru
                return redirect()->route('filament.admin.resources.arsip.3d.folder.index', [
                    'folder' => $this->folder->slug
                ]);
            });
    }

    public function refreshData()
    {
        $this->loadSubfolders();
        $this->dispatch('$refresh');
    }

    // FIX: Override method getTableRecords untuk memastikan data ter-load
    public function getTableRecords(): Collection
    {
        $query = $this->getTableQuery();
        return $query->get();
    }
}
