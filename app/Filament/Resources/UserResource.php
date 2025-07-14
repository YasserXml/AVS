<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use App\Notifications\UserVerifiedByAdmin;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Group;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Pages\Page;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Spatie\Permission\Models\Role;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationGroup = 'Manajemen Pengguna';

    protected static ?string $navigationLabel = 'Pengguna';

    protected static ?string $activeNavigationIcon = 'heroicon-s-user-group';

    protected static ?string $modelLabel = 'Pengguna';

    protected static ?string $pluralModelLabel = 'Pengguna';

    protected static ?string $slug = 'pengguna';

    protected static ?int $navigationSort = 1;

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'danger';
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Jumlah Pengguna Terdaftar';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Informasi Akun')
                    ->description('Data dasar pengguna sistem')
                    ->icon('heroicon-o-identification')
                    ->collapsible()
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label('Nama Pengguna')
                                    ->required()
                                    ->maxLength(255)
                                    ->prefixIcon('heroicon-o-user'),

                                Forms\Components\TextInput::make('email')
                                    ->label('Alamat Email')
                                    ->email()
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(ignoreRecord: true)
                                    ->prefixIcon('heroicon-o-envelope'),
                            ]),

                        Forms\Components\TextInput::make('password')
                            ->label('Kata Sandi')
                            ->password()
                            ->revealable()
                            ->dehydrateStateUsing(fn($state) => filled($state) ? Hash::make($state) : null)
                            ->dehydrated(fn($state) => filled($state))
                            ->required(fn(string $operation): bool => $operation === 'create')
                            ->placeholder('Masukkan kata sandi baru')
                            ->autocomplete('new-password')
                            ->prefixIcon('heroicon-o-lock-closed'),

                        Grid::make(2)
                            ->schema([
                                Forms\Components\Toggle::make('admin_verified')
                                    ->label('Verifikasi Admin')
                                    ->helperText('Aktifkan untuk memverifikasi pengguna secara manual')
                                    ->inline(false)
                                    ->onIcon('heroicon-s-shield-check')
                                    ->offIcon('heroicon-s-shield-exclamation'),

                                Forms\Components\Toggle::make('is_admin')
                                    ->label('Administrator')
                                    ->helperText('Berikan hak akses administrator')
                                    ->inline(false)
                                    ->onIcon('heroicon-s-key')
                                    ->offIcon('heroicon-o-key'),
                            ]),
                    ])
                    ->columns(1),

                Section::make('Hak Akses')
                    ->description('Pengaturan peran dan hak akses pengguna')
                    ->icon('heroicon-o-key')
                    ->collapsible()
                    ->schema([
                        Forms\Components\Select::make('roles')
                            ->label('Peran Pengguna')
                            ->relationship('roles', 'name')
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->columnSpanFull()
                            ->helperText('Pilih satu atau lebih peran untuk pengguna ini')
                            ->prefixIcon('heroicon-o-user-group')
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultPaginationPageOption(25)
            ->poll('60s')
            ->striped()
            ->columns([
                Tables\Columns\TextColumn::make('index')
                    ->label('No')
                    ->state(static function (Tables\Contracts\HasTable $livewire, \stdClass $rowLoop): string {
                        return (string) (
                            $rowLoop->iteration +
                            ($livewire->getTableRecordsPerPage() * (
                                $livewire->getTablePage() - 1
                            ))
                        );
                    })
                    ->alignCenter()
                    ->color('gray')
                    ->weight(FontWeight::Bold)
                    ->searchable(false)
                    ->sortable(false),

                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Pengguna')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Bold)
                    ->description(fn(User $record): string => $record->email)
                    ->copyable(),

                TextColumn::make('roles.name')
                    ->label('Role Pengguna')
                    ->badge()
                    ->color(fn(string $state): string => match (true) {
                        str_contains(strtolower($state), 'super_admin') => 'success',
                        str_contains(strtolower($state), 'admin') => 'danger',
                        str_contains(strtolower($state), 'direktur') => 'warning',
                        str_contains(strtolower($state), 'kepala') => 'info',
                        str_contains(strtolower($state), 'user') => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn($state) => ucwords(str_replace('_', ' ', $state)))
                    ->searchable()
                    ->sortable()
                    ->getStateUsing(fn(User $record): string => $record->roles->pluck('name')->implode(', '))
                    ->placeholder('Tidak ada peran'),

                IconColumn::make('email_verified_at')
                    ->label('Email Terverifikasi')
                    ->boolean()
                    ->alignCenter()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('gray')
                    ->size(IconColumn\IconColumnSize::Large)
                    ->tooltip(
                        fn(User $record): string =>
                        $record->email_verified_at
                            ? 'Email terverifikasi pada ' . $record->email_verified_at->format('d/m/Y H:i')
                            : 'Email belum terverifikasi'
                    )
                    ->getStateUsing(fn(User $record): bool => $record->email_verified_at !== null),

                IconColumn::make('admin_verified')
                    ->label('Diverifikasi Admin')
                    ->boolean()
                    ->alignCenter()
                    ->trueIcon('heroicon-o-shield-check')
                    ->falseIcon('heroicon-o-shield-exclamation')
                    ->trueColor('success')
                    ->falseColor('gray')
                    ->size(IconColumn\IconColumnSize::Large)
                    ->tooltip(
                        fn(User $record): string =>
                        $record->admin_verified
                            ? 'Diverifikasi oleh admin'
                            : 'Belum diverifikasi admin'
                    ),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Terdaftar Pada')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->color('gray')
                    ->icon('heroicon-o-calendar')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Diperbarui Pada')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->color('gray')
                    ->icon('heroicon-o-clock')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('admin_verified')
                    ->label('Status Verifikasi Admin')
                    ->placeholder('Semua Pengguna')
                    ->trueLabel('Terverifikasi Admin')
                    ->falseLabel('Belum Terverifikasi Admin')
                    ->indicator('Status Verifikasi'),

                TernaryFilter::make('email_verified_at')
                    ->label('Status Verifikasi Email')
                    ->placeholder('Semua Pengguna')
                    ->trueLabel('Email Terverifikasi')
                    ->falseLabel('Email Belum Terverifikasi')
                    ->queries(
                        true: fn($query) => $query->whereNotNull('email_verified_at'),
                        false: fn($query) => $query->whereNull('email_verified_at'),
                    )
                    ->indicator('Verifikasi Email'),

                Tables\Filters\SelectFilter::make('divisi')
                    ->label('Divisi')
                    ->options(function () {
                        return User::whereNotNull('divisi')
                            ->distinct()
                            ->pluck('divisi', 'divisi')
                            ->sort();
                    })
                    ->searchable()
                    ->placeholder('Semua Divisi'),

                // Filter khusus untuk menampilkan kandidat kepala divisi per divisi
               
                Tables\Filters\SelectFilter::make('roles')
                    ->label('Peran')
                    ->relationship('roles', 'name')
                    ->searchable()
                    ->preload()
                    ->placeholder('Semua Peran'),
            ])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->label('Lihat')
                        ->color('info')
                        ->icon('heroicon-o-eye'),

                    Tables\Actions\EditAction::make()
                        ->label('Edit')
                        ->color('warning')
                        ->icon('heroicon-o-pencil-square'),

                    Tables\Actions\DeleteAction::make()
                        ->label('Hapus')
                        ->color('danger')
                        ->icon('heroicon-o-trash')
                        ->requiresConfirmation()
                        ->modalHeading('Hapus Pengguna')
                        ->modalDescription(fn(User $record): string => "Apakah Anda yakin ingin menghapus {$record->name}? Tindakan ini tidak dapat dibatalkan.")
                        ->modalSubmitActionLabel('Ya, Hapus Pengguna'),
                ])
                    ->color('gray')
                    ->icon('heroicon-m-cog-6-tooth')
                    ->label('Aksi')
                    ->size('sm')
                    ->tooltip('Kelola pengguna')
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Hapus Dipilih')
                        ->icon('heroicon-o-trash')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalHeading('Hapus Pengguna Yang Dipilih')
                        ->modalDescription('Apakah Anda yakin ingin menghapus semua pengguna yang dipilih? Tindakan ini tidak dapat dibatalkan.')
                        ->modalSubmitActionLabel('Ya, Hapus Semua'),
                ])
                    ->icon('heroicon-m-adjustments-horizontal')
                    ->color('danger')
                    ->tooltip('Aksi untuk pengguna yang dipilih'),
            ])
            ->emptyStateHeading('Belum Ada Pengguna')
            ->emptyStateDescription('Mulai dengan menambahkan pengguna pertama ke sistem.')
            ->emptyStateIcon('heroicon-o-user-group');
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
