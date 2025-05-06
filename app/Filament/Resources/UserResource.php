<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use App\Notifications\UserApprovedNotification;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action as ActionsAction;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-circle';

    protected static ?string $navigationGroup = 'Hak Akses';

    protected static ?string $navigationLabel = 'Pengguna Aplikasi';

    protected static ?int $navigationSort = 1;

    protected static ?string $slug = 'pengguna';

    protected static ?string $modelLabel = 'Pengguna';

    protected static ?string $pluralModelLabel = 'Pengguna';

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'info';
    }



    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Informasi Pengguna')
                    ->description('Masukkan data untuk pengguna baru')
                    ->icon('heroicon-o-user')
                    ->schema([
                        Grid::make()
                            ->columns(2)
                            ->schema([
                                TextInput::make('name')
                                    ->label('Nama Pengguna')
                                    ->placeholder('Masukkan nama lengkap pengguna')
                                    ->required()
                                    ->maxLength(255)
                                    ->prefixIcon('heroicon-o-user-circle'),

                                TextInput::make('email')
                                    ->label('Email Pengguna')
                                    ->placeholder('Masukkan alamat email aktif')
                                    ->email()
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(255)
                                    ->prefixIcon('heroicon-o-envelope'),

                                TextInput::make('password')
                                    ->label('Kata Sandi')
                                    ->placeholder('Masukkan kata sandi yang kuat')
                                    ->password()
                                    ->required(fn($livewire) => $livewire instanceof \Filament\Resources\Pages\CreateRecord)
                                    ->confirmed()
                                    ->revealable()
                                    ->dehydrateStateUsing(fn($state) => Hash::make($state))
                                    ->dehydrated(fn($state) => filled($state))
                                    ->prefixIcon('heroicon-o-lock-closed'),

                                TextInput::make('password_confirmation')
                                    ->label('Konfirmasi Kata Sandi')
                                    ->placeholder('Ketik ulang kata sandi')
                                    ->password()
                                    ->required(fn($livewire) => $livewire instanceof \Filament\Resources\Pages\CreateRecord)
                                    ->revealable()
                                    ->dehydrated(false)
                                    ->visible(fn($livewire) => $livewire instanceof \Filament\Resources\Pages\CreateRecord)
                                    ->prefixIcon('heroicon-o-lock-closed'),
                            ]),
                    ]),
                Section::make('Peran dan Hak Akses')
                    ->description('Tetapkan peran untuk pengguna ini')
                    ->icon('heroicon-o-shield-check')
                    ->schema([
                        Select::make('role')
                            ->label('Peran Pengguna')
                            ->placeholder('Pilih peran yang sesuai')
                            ->relationship('roles', 'name')
                            ->preload()
                            ->searchable()
                            ->required()
                            ->native(false)
                    ]),
            ])
            ->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),
                Tables\Columns\IconColumn::make('email_verified_at')
                    ->label('Email Terverifikasi')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle'),
                Tables\Columns\IconColumn::make('admin_verified')
                    ->label('Verifikasi Admin')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle'),
                Tables\Columns\IconColumn::make('is_admin')
                    ->label('Admin')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal Daftar')
                    ->dateTime('d-m-Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\Filter::make('verified')
                    ->query(fn (Builder $query): Builder => $query->where('admin_verified', true))
                    ->label('Terverifikasi'),
                Tables\Filters\Filter::make('unverified')
                    ->query(fn (Builder $query): Builder => $query->where('admin_verified', false))
                    ->label('Belum Terverifikasi'),
                Tables\Filters\Filter::make('admin')
                    ->query(fn (Builder $query): Builder => $query->where('is_admin', true))
                    ->label('Admin'),
            ])
            ->actions([
                ActionsAction::make('verify')
                    ->label('Verifikasi')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->visible(fn (User $record) => !$record->admin_verified)
                    ->action(function (User $record) {
                        $record->admin_verified = true;
                        $record->save();
                        
                        // Notify the user that their account has been verified
                        self::notifyUserVerified($record);
                        
                        Notification::make()
                            ->title('Pengguna berhasil diverifikasi')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('verify_selected')
                        ->label('Verifikasi Terpilih')
                        ->icon('heroicon-o-check-circle')
                        ->action(function ($records) {
                            foreach ($records as $record) {
                                if (!$record->admin_verified) {
                                    $record->admin_verified = true;
                                    $record->save();
                                    
                                    // Notify each user
                                    self::notifyUserVerified($record);
                                }
                            }
                            
                            Notification::make()
                                ->title('Semua pengguna terpilih berhasil diverifikasi')
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion(),
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
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
