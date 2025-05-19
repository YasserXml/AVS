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

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationGroup = 'Manajemen Pengguna';

    protected static ?string $navigationLabel = 'Pengguna';

    protected static ?string $activeNavigationIcon = 'heroicon-o-user';
    
    protected static ?string $modelLabel = 'Pengguna';

    protected static ?string $pluralModelLabel = 'Pengguna';

    protected static ?string $slug = 'pengguna';

    protected static ?int $navigationSort = 1;

    // Menggunakan warna merah untuk tema
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): string
    {
        return 'danger';
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
                                    ->label('Nama Lengkap')
                                    ->required()
                                    ->placeholder('Masukkan nama lengkap')
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('email')
                                    ->label('Alamat Email')
                                    ->email()
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(ignoreRecord: true),
                            ]),
                        Forms\Components\TextInput::make('password')
                            ->label('Kata Sandi')
                            ->password()
                            ->dehydrateStateUsing(fn($state) => filled($state) ? Hash::make($state) : null)
                            ->dehydrated(fn($state) => filled($state))
                            ->required(fn(string $operation): bool => $operation === 'create')
                            ->placeholder('Masukkan kata sandi baru')
                            ->hint('Kosongkan jika tidak ingin mengubah kata sandi')
                            ->autocomplete('new-password'),
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
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultPaginationPageOption(10)
            ->poll('60s')
            ->columns([
                Tables\Columns\TextColumn::make('index')
                    ->label('No')
                    ->state(static function ($rowLoop): string {
                        return (string) $rowLoop->iteration;
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
                    ->label('Peran')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'Admin' => 'danger',
                        'Super Admin' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn($state) => ucfirst($state))
                    ->searchable()
                    ->sortable()
                    ->getStateUsing(fn(User $record): string => $record->roles->pluck('name')->implode(', ')),

                IconColumn::make('email_verified_at')
                    ->label('Email Terverifikasi')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('gray')
                    ->size(IconColumn\IconColumnSize::Large)
                    ->getStateUsing(fn(User $record): bool => $record->email_verified_at !== null),

                IconColumn::make('admin_verified')
                    ->label('Verifikasi Admin')
                    ->boolean()
                    ->trueIcon('heroicon-o-shield-check')
                    ->falseIcon('heroicon-o-shield-exclamation')
                    ->trueColor('succes')  // Merah untuk yang sudah terverifikasi
                    ->falseColor('gray')
                    ->size(IconColumn\IconColumnSize::Large)
                    ->getStateUsing(fn(User $record): bool => $record->admin_verified !== null),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Terdaftar Pada')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->color('gray')
                    ->icon('heroicon-o-calendar')
                    ->toggleable(),
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
            ])
            ->actions([
                ActionGroup::make([
                    Action::make('edit')
                        ->label('Edit')
                        ->color('info')
                        ->icon('heroicon-o-pencil-square')
                        ->url(fn(User $record): string => UserResource::getUrl('edit', ['record' => $record])),
                    Action::make('delete')
                        ->label('Hapus')
                        ->color('danger')
                        ->icon('heroicon-o-trash')
                        ->requiresConfirmation()
                        ->modalHeading('Hapus Pengguna')
                        ->modalDescription(fn(User $record): string => "Apakah Anda yakin ingin menghapus {$record->name}? Tindakan ini tidak dapat dibatalkan.")
                        ->modalSubmitActionLabel('Ya, Hapus Pengguna')
                ])
                    ->color('danger')
                    ->icon('heroicon-m-cog-6-tooth')
                    ->label('Aksi')
                    ->size('md')
                    ->tooltip('Kelola pengguna')
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Hapus Massal')
                        ->icon('heroicon-o-trash')
                        ->color('danger')
                        ->modalHeading('Hapus Pengguna Massal')
                        ->modalDescription('Apakah Anda yakin ingin menghapus semua pengguna yang dipilih? Tindakan ini tidak dapat dibatalkan.')
                        ->modalSubmitActionLabel('Ya, Hapus Semua')
                ])
                    ->label('Aksi Massal')
                    ->icon('heroicon-m-adjustments-horizontal')
                    ->color('danger')
                    ->tooltip('Aksi untuk pengguna yang dipilih'),
            ])
            ->emptyStateHeading('Belum Ada Pengguna')
            ->emptyStateDescription('Tambahkan pengguna baru untuk mengakses sistem.')
            ->emptyStateIcon('heroicon-o-user-plus')
            ->emptyStateActions([
                Tables\Actions\Action::make('createUser')
                    ->label('Tambah Pengguna')
                    ->url(route('filament.admin.resources.pengguna.create'))
                    ->icon('heroicon-o-user-plus')
                    ->color('danger')
            ]);
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
