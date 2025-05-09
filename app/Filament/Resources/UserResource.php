<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Actions\VerifyUserAction;
use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use App\Notifications\AdminVerifiedUser;
use App\Notifications\UserVerifiedByAdmin;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'Manajemen Pengguna';

    protected static ?string $navigationLabel = 'Pengguna';

    protected static ?string $modelLabel = 'Pengguna';

    protected static ?string $pluralModelLabel = 'Pengguna';

    protected static ?string $slug = 'pengguna';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nama')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                Forms\Components\TextInput::make('password')
                    ->label('Password')
                    ->password()
                    ->dehydrateStateUsing(fn($state) => Hash::make($state))
                    ->dehydrated(fn($state) => filled($state))
                    ->required(fn(string $operation): bool => $operation === 'create'),
                Forms\Components\Toggle::make('admin_verified')
                    ->label('Verifikasi Admin')
                    ->helperText('Pengguna dapat login jika telah diverifikasi')
                    ->default(false),
                Forms\Components\DateTimePicker::make('email_verified_at')
                    ->label('Email Terverifikasi Pada')
                    ->placeholder('Belum diverifikasi'),
                Forms\Components\Select::make('roles')
                    ->label('Peran')
                    ->relationship('roles', 'name')
                    ->multiple()
                    ->searchable()
                    ->preload(),
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
                    ->label('Email')
                    ->searchable(),
                TextColumn::make('roles.name')
                    ->label('Peran')
                    ->searchable()
                    ->sortable()
                    ->getStateUsing(fn(User $record): string => $record->roles->pluck('name')->implode(', ')),
                Tables\Columns\IconColumn::make('email_verified_at')
                    ->label('Email Terverifikasi')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->getStateUsing(fn(User $record): bool => $record->email_verified_at !== null),
                Tables\Columns\IconColumn::make('admin_verified')
                    ->label('Verifikasi Admin')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal Daftar')
                    ->dateTime('d/m/Y H:i')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('admin_verified')
                    ->label('Status Verifikasi')
                    ->options([
                        '1' => 'Terverifikasi',
                        '0' => 'Belum Terverifikasi',
                    ]),
            ])
            ->actions([
                ActionGroup::make([
                    Action::make('edit')
                        ->label('Edit')
                        ->color('info')
                        ->icon('heroicon-o-pencil')
                        ->url(fn(User $record): string => UserResource::getUrl('edit', ['record' => $record])),
                    Action::make('delete')
                        ->label('Hapus')
                        ->color('danger')
                        ->icon('heroicon-o-trash')
                        ->action(function (User $record): void {
                            $record->delete();
                            Notification::make()
                                ->title('Pengguna Dihapus')
                                ->success()
                                ->body("Pengguna {$record->name} telah dihapus.")
                                ->icon('heroicon-o-check-circle')
                                ->send();
                        }),

                    
                ]),


            ])
            ->bulkActions([
               
                Tables\Actions\DeleteBulkAction::make(),
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
