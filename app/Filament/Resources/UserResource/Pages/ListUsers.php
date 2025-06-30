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
            'semua' => Tab::make('Semua Pengguna')
                ->badge(fn() => User::count())
                ->badgeColor('info'),

            'administrator' => Tab::make('Administrator')
                ->badge(fn() => User::role(['super_admin', 'admin'])->count())
                ->badgeColor('danger')
                ->modifyQueryUsing(fn(Builder $query) => $query->role(['super_admin', 'admin'])),

            'direktur' => Tab::make('Direktur')
                ->badge(fn() => User::whereHas('roles', fn($q) => $q->where('name', 'like', 'direktur_%'))->count())
                ->badgeColor('warning')
                ->modifyQueryUsing(
                    fn(Builder $query) =>
                    $query->whereHas('roles', fn($q) => $q->where('name', 'like', 'direktur_%'))
                ),

            'kepala_divisi' => Tab::make('Kepala Divisi')
                ->badge(fn() => User::whereHas('roles', fn($q) => $q->where('name', 'like', 'kepala_divisi_%'))->count())
                ->badgeColor('info')
                ->modifyQueryUsing(
                    fn(Builder $query) =>
                    $query->whereHas('roles', fn($q) => $q->where('name', 'like', 'kepala_divisi_%'))
                ),
                
            'divisi_hrd' => Tab::make('HRD & GA')
                ->badge(fn() => User::role(['divisi_manager_hrd', 'divisi_hrd_ga', 'kepala_divisi_hrd_ga'])->count())
                ->badgeColor('success')
                ->modifyQueryUsing(
                    fn(Builder $query) =>
                    $query->role(['divisi_manager_hrd', 'divisi_hrd_ga', 'kepala_divisi_hrd_ga'])
                ),

            'divisi_keuangan' => Tab::make('Keuangan')
                ->badge(fn() => User::role(['divisi_keuangan', 'kepala_divisi_keuangan'])->count())
                ->badgeColor('success')
                ->modifyQueryUsing(
                    fn(Builder $query) =>
                    $query->role(['divisi_keuangan', 'kepala_divisi_keuangan'])
                ),

            'divisi_software' => Tab::make('Software')
                ->badge(fn() => User::role(['divisi_software', 'kepala_divisi_software'])->count())
                ->badgeColor('info')
                ->modifyQueryUsing(
                    fn(Builder $query) =>
                    $query->role(['divisi_software', 'kepala_divisi_software'])
                ),

            'divisi_elektro' => Tab::make('Elektro')
                ->badge(fn() => User::role(['divisi_elektro', 'kepala_divisi_elektro'])->count())
                ->badgeColor('info')
                ->modifyQueryUsing(
                    fn(Builder $query) =>
                    $query->role(['divisi_elektro', 'kepala_divisi_elektro'])
                ),

            'divisi_rd' => Tab::make('R&D')
                ->badge(fn() => User::role(['divisi_rnd', 'kepala_divisi_rnd'])->count())
                ->badgeColor('info')
                ->modifyQueryUsing(
                    fn(Builder $query) =>
                    $query->role(['divisi_rnd', 'kepala_divisi_rnd'])
                ),

            'divisi_3d' => Tab::make('3D')
                ->badge(fn() => User::role(['divisi_3d', 'kepala_divisi_3d'])->count())
                ->badgeColor('warning')
                ->modifyQueryUsing(
                    fn(Builder $query) =>
                    $query->role(['divisi_3d', 'kepala_divisi_3d'])
                ),

            'divisi_mekanik' => Tab::make('Mekanik')
                ->badge(fn() => User::role(['divisi_mekanik', 'kepala_divisi_mekanik'])->count())
                ->badgeColor('primary')
                ->modifyQueryUsing(
                    fn(Builder $query) =>
                    $query->role(['divisi_mekanik', 'kepala_divisi_mekanik'])
                ),

            'divisi_purchasing' => Tab::make('Purchasing')
                ->badge(fn() => User::role(['divisi_purchasing', 'kepala_divisi_purchasing'])->count())
                ->badgeColor('success')
                ->modifyQueryUsing(
                    fn(Builder $query) =>
                    $query->role(['divisi_purchasing', 'kepala_divisi_purchasing'])
                ),

            'divisi_pmo' => Tab::make('PMO')
                ->badge(fn() => User::role(['divisi_pmo', 'kepala_divisi_pmo'])->count())
                ->badgeColor('primary')
                ->modifyQueryUsing(
                    fn(Builder $query) =>
                    $query->role(['divisi_pmo', 'kepala_divisi_pmo'])
                ),
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
