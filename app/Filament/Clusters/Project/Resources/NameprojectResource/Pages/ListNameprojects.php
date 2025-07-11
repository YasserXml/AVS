<?php

namespace App\Filament\Clusters\Project\Resources\NameprojectResource\Pages;

use App\Filament\Clusters\Project\Resources\NameprojectResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;

class ListNameprojects extends ListRecords
{
    protected static string $resource = NameprojectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Tambah Project')
                ->icon('heroicon-o-plus')
                ->iconPosition('before'),
        ];
    }

    public function getTitle(): string|Htmlable
    {
        return new HtmlString('
            <div class="flex items-center gap-2 ">
                <span class="text-xl font-bold">Project</span>
            </div>
        ');
    }
}
