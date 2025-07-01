<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Models\User;
use Filament\Actions;
use Filament\Forms\Components\Tabs;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Tambah Pengguna')
                ->color('success')
                ->icon('heroicon-o-plus'),
        ];
    }

    public function getTabs(): array
    {
        return [
            'Semua Pengguna' => Tab::make()
                ->badge(fn() => $this->getModel()::count())
                ->badgeColor('info'),
            'Administrator' => Tab::make()
                ->badge(fn() => $this->getModel()::role(['super_admin', 'admin'])->count())
                ->badgeColor('success')
                ->modifyQueryUsing(fn($query) => $query->role(['super_admin', 'admin'])),
            'Divisi Manager HRD' => Tab::make()
                ->badge(fn() => $this->getModel()::role('user_divisi_manager_hrd')->count())
                ->badgeColor('success')
                ->modifyQueryUsing(fn($query) => $query->role('user_divisi_manager_hrd')),
            'Divisi HRD & GA' => Tab::make()
                ->badge(fn() => $this->getModel()::role('user_divisi_hrd_ga')->count())
                ->badgeColor('success')
                ->modifyQueryUsing(fn($query) => $query->role('user_divisi_hrd_ga')),
            'Divisi Keuangan' => Tab::make()
                ->badge(fn() => $this->getModel()::role('user_divisi_keuangan')->count())
                ->badgeColor('success')
                ->modifyQueryUsing(fn($query) => $query->role('user_divisi_keuangan')),
            'Divisi Purchasing' => Tab::make()
                ->badge(fn() => $this->getModel()::role('user_divisi_purchasing')->count())
                ->badgeColor('success')
                ->modifyQueryUsing(fn($query) => $query->role('user_divisi_purchasing')),
            'Divisi Software' => Tab::make()
                ->badge(fn() => $this->getModel()::role('user_divisi_software')->count())
                ->badgeColor('info')
                ->modifyQueryUsing(fn($query) => $query->role('user_divisi_software')),
            'Divisi Pmo' => Tab::make()
                ->badge(fn() => $this->getModel()::role('user_divisi_pmo')->count())
                ->badgeColor('info')
                ->modifyQueryUsing(fn($query) => $query->role('user_divisi_pmo')),
            'Divisi Elektro' => Tab::make()
                ->badge(fn() => $this->getModel()::role('user_divisi_elektro')->count())
                ->badgeColor('info')
                ->modifyQueryUsing(fn($query) => $query->role('user_divisi_elektro')),
            'Divisi R&D' => Tab::make()
                ->badge(fn() => $this->getModel()::role('user_divisi_r&d')->count())
                ->badgeColor('info')
                ->modifyQueryUsing(fn($query) => $query->role('user_divisi_r&d')),
            'Divisi 3D' => Tab::make()
                ->badge(fn() => $this->getModel()::role('user_divisi_3d')->count())
                ->badgeColor('warning')
                ->modifyQueryUsing(fn($query) => $query->role('user_divisi_3d')),
            'Divisi Mekanik' => Tab::make()
                ->badge(fn() => $this->getModel()::role('user_divisi_mekanik')->count())
                ->badgeColor('primary')
                ->modifyQueryUsing(fn($query) => $query->role('user_divisi_mekanik')),
        ];
    } 
    

    public function getTitle(): string|Htmlable
    {
        return new HtmlString('
            <div class="flex items-center gap-2 ">
                <span class="text-xl font-bold">Pengguna</span>
            </div>
        ');
    }
}
