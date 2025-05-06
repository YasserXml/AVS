<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use App\Notifications\UserVerifiedNotification;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Hash;
use Filament\Tables\Actions\Action;
use Filament\Notifications\Notification;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    
    protected static ?string $navigationGroup = 'Manajemen Pengguna';
    
    protected static ?int $navigationSort = 1;
    
    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nama')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                Forms\Components\TextInput::make('password')
                    ->password()
                    ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                    ->dehydrated(fn ($state) => filled($state))
                    ->required(fn (string $operation): bool => $operation === 'create'),
                Forms\Components\Toggle::make('admin_verified')
                    ->label('Terverifikasi Admin')
                    ->helperText('Pengguna yang belum diverifikasi admin tidak dapat login')
                    ->default(false),
                Forms\Components\Toggle::make('is_admin')
                    ->label('Admin')
                    ->helperText('Berikan hak akses admin ke pengguna ini')
                    ->default(false),
                Forms\Components\Select::make('roles')
                    ->label('Peran')
                    ->relationship('roles', 'name')
                    ->multiple()
                    ->preload()
                    ->searchable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\IconColumn::make('email_verified_at')
                    ->label('Email Terverifikasi')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-badge')
                    ->falseIcon('heroicon-o-x-mark')
                    ->getStateUsing(fn (User $record): bool => $record->hasVerifiedEmail()),
                Tables\Columns\IconColumn::make('admin_verified')
                    ->label('Diverifikasi Admin')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-badge')
                    ->falseIcon('heroicon-o-x-mark'),
                Tables\Columns\TextColumn::make('roles.name')
                    ->label('Peran')
                    ->badge()
                    ->color('primary'),
                Tables\Columns\TextColumn::make('provider')
                    ->label('Provider'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Terdaftar Pada')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\Filter::make('unverified')
                    ->label('Belum Diverifikasi')
                    ->query(fn (Builder $query): Builder => $query->where('admin_verified', false)),
                Tables\Filters\Filter::make('verified')
                    ->label('Sudah Diverifikasi')
                    ->query(fn (Builder $query): Builder => $query->where('admin_verified', true)),
                Tables\Filters\Filter::make('admin')
                    ->label('Admin')
                    ->query(fn (Builder $query): Builder => $query->where('is_admin', true)),
            ])
            ->actions([
                Action::make('verify')
                    ->label('Verifikasi')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (User $record): bool => !$record->admin_verified)
                    ->action(function (User $record): void {
                        $record->admin_verified = true;
                        $record->save();
                        
                        // Kirim notifikasi ke user
                        $record->notify(new UserVerifiedNotification());
                        
                        Notification::make()
                            ->title('Pengguna Berhasil Diverifikasi')
                            ->success()
                            ->send();
                    }),
                    
                Action::make('reject')
                    ->label('Tolak')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (User $record): bool => !$record->admin_verified)
                    ->requiresConfirmation()
                    ->action(function (User $record): void {
                        $record->delete();
                        
                        Notification::make()
                            ->title('Pendaftaran Pengguna Ditolak')
                            ->success()
                            ->send();
                    }),
                    
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    
                    Tables\Actions\BulkAction::make('verifySelected')
                        ->label('Verifikasi Terpilih')
                        ->icon('heroicon-o-check-circle')
                        ->action(function ($records) {
                            foreach ($records as $record) {
                                if (!$record->admin_verified) {
                                    $record->admin_verified = true;
                                    $record->save();
                                    
                                    // Kirim notifikasi ke user
                                    $record->notify(new UserVerifiedNotification());
                                }
                            }
                            
                            Notification::make()
                                ->title('Semua Pengguna Terpilih Berhasil Diverifikasi')
                                ->success()
                                ->send();
                        }),
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