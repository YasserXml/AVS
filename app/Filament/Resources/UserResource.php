<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use App\Notifications\UserVerifiedNotification;
use Filament\Forms;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Hash;
use Filament\Tables\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\BooleanColumn;
use Filament\Tables\Columns\TextColumn;

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
                TextInput::make('name')
                    ->label('Nama')
                    ->required()
                    ->maxLength(255),
                TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->unique(static::getModel(), 'email', fn($record) => $record),
                TextInput::make('password')
                    ->label('Kata Sandi')
                    ->password()
                    ->dehydrateStateUsing(fn($state) => Hash::make($state))
                    ->dehydrated(fn($state) => filled($state))
                    ->required(fn($record) => ! $record)
                    ->maxLength(255),
                Checkbox::make('admin_verified')
                    ->label('Diverifikasi Admin')
                    ->helperText('Centang ini untuk memverifikasi pengguna'),
                Checkbox::make('email_verified_at')
                    ->label('Email Terverifikasi')
                    ->helperText('Menunjukkan apakah email pengguna sudah diverifikasi')
                    ->dehydrateStateUsing(fn($state) => $state ? now() : null)
                    ->formatStateUsing(fn($state) => !is_null($state))
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->searchable()
                    ->sortable(),
                BooleanColumn::make('admin_verified')
                    ->label('Diverifikasi Admin')
                    ->sortable()
                    ->trueIcon('heroicon-o-check')
                    ->falseIcon('heroicon-o-x-circle'),
                BooleanColumn::make('email_verified_at')
                    ->label('Email Terverifikasi')
                    ->getStateUsing(fn(User $record) => !is_null($record->email_verified_at))
                    ->sortable()
                    ->trueIcon('heroicon-o-check')
                    ->falseIcon('heroicon-o-x-circle'),
                TextColumn::make('created_at')
                    ->label('Terdaftar Pada')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\Filter::make('verified')
                    ->label('Sudah Diverifikasi')
                    ->query(fn($query) => $query->where('admin_verified', true)),
                Tables\Filters\Filter::make('unverified')
                    ->label('Belum Diverifikasi')
                    ->query(fn($query) => $query->where('admin_verified', false)),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('Edit')
                    ->color('info')
                    ->icon('heroicon-o-pencil'),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('verify')
                    ->label('Verifikasi Pengguna')
                    ->icon('heroicon-o-check')
                    ->action(function ($records) {
                        foreach ($records as $record) {
                            $record->admin_verified = true;

                            if (!$record->hasVerifiedEmail()) {
                                $record->email_verified_at = now();
                            }

                            $record->save();

                            // Kirim notifikasi
                            $record->notify(new UserVerifiedNotification());
                        }
                    })
                    ->deselectRecordsAfterCompletion(),
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
