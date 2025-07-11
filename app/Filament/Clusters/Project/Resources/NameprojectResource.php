<?php

namespace App\Filament\Clusters\Project\Resources;

use App\Filament\Clusters\Project;
use App\Filament\Clusters\Project\Resources\NameprojectResource\Pages;
use App\Filament\Clusters\Project\Resources\NameprojectResource\RelationManagers;
use App\Models\Nameproject;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class NameprojectResource extends Resource
{
    protected static ?string $model = Nameproject::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office';

    protected static ?string $activeNavigationIcon = 'heroicon-s-building-office-2';

    protected static ?string $navigationLabel = 'Project';

    protected static ?string $modelLabel = 'Project';

    protected static ?string $pluralModelLabel = 'Project';

    protected static ?string $slug = 'project';

    protected static ?string $cluster = Project::class;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Informasi Project')
                    ->description('Masukkan detail lengkap project')
                    ->icon('heroicon-o-information-circle')
                    ->schema([
                        TextInput::make('nama_project')
                            ->label('Nama Proyek')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Masukkan nama project...')
                            ->autocomplete(false)
                            ->columnSpanFull(),

                        Select::make('user_id')
                            ->label('PM')
                            ->relationship('user', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->reactive()
                            ->live()
                            ->placeholder('Pilih PM Project...')
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')
                                    ->label('Nama')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('email')
                                    ->label('Email')
                                    ->email()
                                    ->required()
                                    ->maxLength(255),
                            ])
                            ->native(false),
                    ])
                    ->columnSpanFull()
                    ->collapsible(),

                Section::make('Jadwal Proyek')
                    ->description('Tentukan waktu mulai dan selesai project')
                    ->icon('heroicon-o-calendar-days')
                    ->schema([
                        DatePicker::make('tanggal_mulai')
                            ->label('Tanggal Mulai')
                            ->placeholder('Pilih tanggal mulai...')
                            ->displayFormat('d/m/Y ')
                            ->seconds(false)
                            ->native(false),

                        DatePicker::make('tanggal_selesai')
                            ->label('Tanggal Selesai')
                            ->placeholder('Pilih tanggal selesai...')
                            ->displayFormat('d/m/Y ')
                            ->seconds(false)
                            ->native(false)
                            ->after('tanggal_mulai'),
                    ])
                    ->columns(2)
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nama_project')
                    ->label('Nama Project')
                    ->searchable()
                    ->sortable()
                    ->alignCenter()
                    ->weight(FontWeight::Medium)
                    ->color(Color::Blue)
                    ->copyable()
                    ->copyMessage('Nama proyek disalin!')
                    ->wrap(),

                TextColumn::make('user.name')
                    ->label('PM')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-o-user-circle')
                    ->color(Color::Purple)
                    ->default('Tidak ada')
                    ->badge(),

                TextColumn::make('tanggal_mulai')
                    ->label('Tanggal Mulai')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->icon('heroicon-o-play-circle')
                    ->placeholder('Belum ditentukan')
                    ->toggleable(),

                TextColumn::make('tanggal_selesai')
                    ->label('Tanggal Selesai')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->icon('heroicon-o-stop-circle')
                    ->placeholder('Belum ditentukan')
                    ->toggleable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->getStateUsing(function ($record) {
                        if (!$record->tanggal_mulai || !$record->tanggal_selesai) {
                            return 'Belum Dijadwalkan';
                        }

                        $now = now();
                        $start = $record->tanggal_mulai;
                        $end = $record->tanggal_selesai;

                        if ($now < $start) {
                            return 'Belum Dimulai';
                        } elseif ($now >= $start && $now <= $end) {
                            return 'Sedang Berjalan';
                        } else {
                            return 'Selesai';
                        }
                    })
                    ->color(fn(string $state): string => match ($state) {
                        'Belum Dijadwalkan' => 'gray',
                        'Belum Dimulai' => 'warning',
                        'Sedang Berjalan' => 'success',
                        'Selesai' => 'danger',
                    })
                    ->icon(fn(string $state): string => match ($state) {
                        'Belum Dijadwalkan' => 'heroicon-o-question-mark-circle',
                        'Belum Dimulai' => 'heroicon-o-clock',
                        'Sedang Berjalan' => 'heroicon-o-play',
                        'Selesai' => 'heroicon-o-check-circle',
                    }),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->since()
                    ->tooltip(fn($record) => $record->created_at->format('d/m/Y H:i:s'))
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->since()
                    ->tooltip(fn($record) => $record->updated_at->format('d/m/Y H:i:s'))
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make()
                    ->label('Status Penghapusan')
                    ->placeholder('Semua')
                    ->trueLabel('Hanya yang dihapus')
                    ->falseLabel('Tanpa yang dihapus')
                    ->native(false),

                Tables\Filters\SelectFilter::make('user_id')
                    ->label('PM')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload()
                    ->placeholder('Semua pemilik')
                    ->native(false),

                Tables\Filters\Filter::make('tanggal_mulai')
                    ->label('Rentang Tanggal Mulai')
                    ->form([
                        DateTimePicker::make('dari')
                            ->label('Dari')
                            ->placeholder('Pilih tanggal mulai...')
                            ->displayFormat('d/m/Y')
                            ->native(false),
                        DateTimePicker::make('sampai')
                            ->label('Sampai')
                            ->placeholder('Pilih tanggal akhir...')
                            ->displayFormat('d/m/Y')
                            ->native(false),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['dari'],
                                fn(Builder $query, $date): Builder => $query->whereDate('tanggal_mulai', '>=', $date),
                            )
                            ->when(
                                $data['sampai'],
                                fn(Builder $query, $date): Builder => $query->whereDate('tanggal_mulai', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if ($data['dari'] ?? null) {
                            $indicators['dari'] = 'Mulai dari: ' . \Carbon\Carbon::parse($data['dari'])->format('d/m/Y');
                        }

                        if ($data['sampai'] ?? null) {
                            $indicators['sampai'] = 'Sampai: ' . \Carbon\Carbon::parse($data['sampai'])->format('d/m/Y');
                        }

                        return $indicators;
                    })
                    ->columns(2),
            ])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->label('Lihat')
                        ->color(Color::Blue),
                    Tables\Actions\EditAction::make()
                        ->label('Edit')
                        ->color(Color::Orange),
                    Tables\Actions\DeleteAction::make()
                        ->label('Hapus')
                        ->color(Color::Red),
                    Tables\Actions\ForceDeleteAction::make()
                        ->label('Hapus Permanen')
                        ->color(Color::Gray),
                    Tables\Actions\RestoreAction::make()
                        ->label('Pulihkan')
                        ->color(Color::Green),
                ])
                    ->icon('heroicon-o-ellipsis-vertical')
                    ->color(Color::Gray)
                    ->button()
                    ,
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Hapus Terpilih'),
                    Tables\Actions\ForceDeleteBulkAction::make()
                        ->label('Hapus Permanen Terpilih'),
                    Tables\Actions\RestoreBulkAction::make()
                        ->label('Pulihkan Terpilih'),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->label('Buat Project Baru')
                    ->icon('heroicon-o-plus'),
            ])
            ->emptyStateHeading('Belum ada project')
            ->emptyStateIcon('heroicon-o-briefcase')
            ->striped()
            ->defaultSort('created_at', 'desc')
            ->persistSortInSession()
            ->persistSearchInSession()
            ->persistFiltersInSession()
            ->poll('30s');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListNameprojects::route('/'),
            'create' => Pages\CreateNameproject::route('/create'),
            'edit' => Pages\EditNameproject::route('/{record}/edit'),
        ];
    }
}
