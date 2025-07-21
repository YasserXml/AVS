<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Notifications\Actions\Action as ActionsAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Contracts\Support\Htmlable;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    public function getTitle(): string|Htmlable
    {
        return "Tambah pengguna baru";
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->title('Berhasil')
            ->body('Data pengguna baru berhasil ditambahkan')
            ->icon('heroicon-o-check-circle')
            ->iconColor('success')
            ->success()
            ->actions([
                ActionsAction::make('view')
                    ->url($this->getResource()::getUrl('index'))
                    ->label('Lihat'),
            ])
            ->seconds(5);
    }
    
}
