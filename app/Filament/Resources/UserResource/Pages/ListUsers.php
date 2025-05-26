<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Resources\Components\Tab as ComponentsTab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;
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
            'Semua Pengguna' => ComponentsTab::make()
                ->badge(fn() => $this->getModel()::count())
                ->badgeColor('info'),
            'Administrator' => ComponentsTab::make()
                ->badge(fn() => $this->getModel()::role(['super_admin', 'admin'])->count())
                ->badgeColor('success')
                ->modifyQueryUsing(fn($query) => $query->role(['super_admin', 'admin'])),
            'Divisi Manager HRD' => ComponentsTab::make()
                ->badge(fn() => $this->getModel()::role('divisi_manager_hrd')->count())
                ->badgeColor('success')
                ->modifyQueryUsing(fn($query) => $query->role('divisi_manager_hrd')),
            'Divisi HRD & GA' => ComponentsTab::make()
                ->badge(fn() => $this->getModel()::role('divisi_hrd_ga')->count())
                ->badgeColor('success')
                ->modifyQueryUsing(fn($query) => $query->role('divisi_hrd_ga')),
            'Divisi Keuangan' => ComponentsTab::make()
                ->badge(fn() => $this->getModel()::role('divisi_keuangan')->count())
                ->badgeColor('success')
                ->modifyQueryUsing(fn($query) => $query->role('divisi_keuangan')),
            'Divisi Purchasing' => ComponentsTab::make()
                ->badge(fn() => $this->getModel()::role('divisi_purchasing')->count())
                ->badgeColor('success')
                ->modifyQueryUsing(fn($query) => $query->role('divisi_purchasing')),
            'Divisi Software' => ComponentsTab::make()
                ->badge(fn() => $this->getModel()::role('divisi_software')->count())
                ->badgeColor('info')
                ->modifyQueryUsing(fn($query) => $query->role('divisi_software')),
            'Divisi Elektro' => ComponentsTab::make()
                ->badge(fn() => $this->getModel()::role('divisi_elektro')->count())
                ->badgeColor('info')
                ->modifyQueryUsing(fn($query) => $query->role('divisi_elektro')),
            'Divisi R&D' => ComponentsTab::make()
                ->badge(fn() => $this->getModel()::role('divisi_r&d')->count())
                ->badgeColor('info')
                ->modifyQueryUsing(fn($query) => $query->role('divisi_r&d')),
            'Divisi 3D' => ComponentsTab::make()
                ->badge(fn() => $this->getModel()::role('divisi_3d')->count())
                ->badgeColor('warning')
                ->modifyQueryUsing(fn($query) => $query->role('divisi_3d')),
            'Divisi Mekanik' => ComponentsTab::make()
                ->badge(fn() => $this->getModel()::role('divisi_mekanik')->count())
                ->badgeColor('primary')
                ->modifyQueryUsing(fn($query) => $query->role('divisi_mekanik')),
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
