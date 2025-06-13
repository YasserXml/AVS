<?php

namespace App\Filament\Resources\DirektoratfolderResource\Pages;

use App\Filament\Resources\DirektoratfolderResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

class CreateDirektoratfolder extends CreateRecord
{
    protected static string $resource = DirektoratfolderResource::class;

     protected function getFormActions(): array
    {
        return [
            $this->getCreateFormAction()
                ->label('Simpan Data')
                ->icon('heroicon-o-check')
                ->size('lg')
                ->extraAttributes([
                    'class' => 'font-semibold'
                ]),
            $this->getCancelFormAction()
                ->label('Batal')
                ->icon('heroicon-o-x-mark')
                ->color('gray'),
        ];
    }

     protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Pastikan folder yang dibuat di halaman utama adalah root folder
        $data['parent_id'] = null;
        $data['model_type'] = null;
        $data['model_id'] = null;
        
        // Set user yang membuat
        $data['user_id'] = filament()->auth()->id();
        $data['user_type'] = get_class(filament()->auth()->user());
        
        // Set collection berdasarkan nama folder jika belum ada
        if (empty($data['collection'])) {
            $data['collection'] = Str::slug($data['name']);
        }
        
        return $data;
    }

    protected function getRedirectUrl(): string
    {
        // Redirect kembali ke halaman list setelah membuat folder
        return $this->getResource()::getUrl('index');
    }
}
