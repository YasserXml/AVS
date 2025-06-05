<?php

namespace App\Filament\Resources\DirektoratmediaResource\Pages;

use App\Filament\Resources\DirektoratmediaResource;
use App\Models\Direktoratfolder;
use App\Models\Direktoratmedia;
use Filament\Actions;
use Illuminate\Support\Str;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;

class ListDirektoratmedia extends ListRecords
{
    protected static string $resource = DirektoratmediaResource::class;

    public ?int $folder_id = null;
    public ?Direktoratfolder $folder = null;

    public function getTitle(): string|Htmlable
    {
        return $this->folder->name; 
    }

    public function mount(): void
    {
        parent::mount();

        if(!request()->has('folder_id')) {
            abort(404, 'Folder ID diperlukan');
        }

        $folder = Direktoratfolder::find(request()->get('folder_id'));
        if(!$folder) {
            abort(404, 'Folder tidak ditemukan');
        }
        else {
            if($folder->is_protected && !session()->has('folder_password')) {
                abort(403, 'Anda tidak dapat mengakses folder ini');
            }
        }

        $this->folder = $folder;
        $this->folder_id = request()->get('folder_id');
        session()->put('folder_id', $this->folder_id);
    }

    protected function getHeaderActions(): array
    {
        $folder_id = $this->folder_id;
        $folder = Direktoratfolder::find($folder_id);

        return [
            $this->createMediaAction($folder_id),
            $this->createSubFolderAction($folder_id),
            $this->deleteFolderAction($folder_id),
            $this->editCurrentFolderAction($folder_id),
        ];
    }

    public function createMediaAction($folder_id)
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
                    ->disk('public')
                    ->directory('media'),
                TextInput::make('title')
                    ->label('Judul')
                    ->maxLength(255),
                TextInput::make('description')
                    ->label('Deskripsi')
                    ->maxLength(500),
            ])
            ->action(function (array $data) use ($folder_id) {
                $folder = Direktoratfolder::find($folder_id);
                
                foreach ($data['files'] as $file) {
                    $media = new Direktoratmedia();
                    $media->collection_name = $folder->collection;
                    $media->name = $data['title'] ?? pathinfo($file, PATHINFO_FILENAME);
                    $media->file_name = $file;
                    $media->mime_type = mime_content_type(storage_path('app/public/' . $file));
                    $media->disk = 'public';
                    $media->size = filesize(storage_path('app/public/' . $file));
                    $media->manipulations = json_encode([]);
                    $media->custom_properties = json_encode([
                        'title' => $data['title'] ?? null,
                        'description' => $data['description'] ?? null,
                    ]);
                    $media->generated_conversions = json_encode([]);
                    $media->responsive_images = json_encode([]);
                    $media->save();
                }

                Notification::make()
                    ->title('Media berhasil diupload')
                    ->success()
                    ->send();
            });
    }

    public function createSubFolderAction($folder_id)
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
                TextInput::make('description')
                    ->label('Deskripsi')
                    ->maxLength(255),
            ])
            ->action(function (array $data) use ($folder_id) {
                $parentFolder = Direktoratfolder::find($folder_id);
                
                $folder = new Direktoratfolder();
                $folder->name = $data['name'];
                $folder->collection = $parentFolder->collection . '/' . Str::slug($data['name']);
                $folder->description = $data['description'] ?? null;
                $folder->model_type = Direktoratfolder::class;
                $folder->model_id = $folder_id;
                $folder->user_id = filament()->auth()->id();
                $folder->user_type = get_class(filament()->auth()->user());
                $folder->save();

                Notification::make()
                    ->title('Sub folder berhasil dibuat')
                    ->success()
                    ->send();
            });
    }

    public function deleteFolderAction($folder_id)
    {
        return Actions\Action::make('deleteFolder')
            ->label('Hapus Folder')
            ->icon('heroicon-o-trash')
            ->color('danger')
            ->requiresConfirmation()
            ->action(function () use ($folder_id) {
                $folder = Direktoratfolder::find($folder_id);
                $folder->delete();

                Notification::make()
                    ->title('Folder berhasil dihapus')
                    ->success()
                    ->send();

                return redirect()->route('filament.admin.resources.direktoratfolders.index');
            });
    }

    public function editCurrentFolderAction($folder_id)
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
                TextInput::make('description')
                    ->label('Deskripsi')
                    ->maxLength(255)
                    ->default(fn() => $this->folder->description),
            ])
            ->action(function (array $data) use ($folder_id) {
                $folder = Direktoratfolder::find($folder_id);
                $folder->update($data);

                Notification::make()
                    ->title('Folder berhasil diubah')
                    ->success()
                    ->send();
            });
    }

    public function folderAction(?Direktoratfolder $item = null)
    {
        return Actions\Action::make('folderAction')
            ->requiresConfirmation(function (array $arguments) {
                if($arguments['record']['is_protected']) {
                    return true;
                }
                else {
                    return false;
                }
            })
            ->form(function (array $arguments) {
                if($arguments['record']['is_protected']) {
                    return [
                        TextInput::make('password')
                            ->label('Password')
                            ->password()
                            ->revealable()
                            ->required()
                            ->maxLength(255),
                    ];
                }
                else {
                    return null;
                }
            })
            ->action(function (array $arguments, array $data) {
                if($arguments['record']['is_protected']) {
                    if($arguments['record']['password'] != $data['password']) {
                        Notification::make()
                            ->title('Password salah')
                            ->danger()
                            ->send();

                        return;
                    }
                    else {
                        session()->put('folder_password', $data['password']);
                    }
                }

                return redirect()->route('filament.admin.resources.direktoratmedia.index', [
                    'folder_id' => $arguments['record']['id']
                ]);
            })
            ->view('folders.folderaction', ['item' => $item]);
    }

    public function deleteMedia()
    {
        return Actions\Action::make('deleteMedia')
            ->label('Hapus Media')
            ->icon('heroicon-s-trash')
            ->color('danger')
            ->requiresConfirmation()
            ->action(function (array $arguments) {
                $media = Direktoratmedia::find($arguments['record']['id']);
                $media->delete();

                Notification::make()
                    ->title('Media berhasil dihapus')
                    ->success()
                    ->send();
            });
    }

    
}
