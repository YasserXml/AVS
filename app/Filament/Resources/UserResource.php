<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Actions\VerifyUserAction;
use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use App\Notifications\UserVerifiedByAdmin;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
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
                Tables\Actions\EditAction::make(),
                // VerifyUserAction::make(),
                Action::make('verify')
                    ->label('Verifikasi')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->visible(fn(User $record): bool => !$record->admin_verified)
                    ->requiresConfirmation()
                    ->modalHeading('Verifikasi Pengguna')
                    ->modalDescription('Apakah Anda yakin ingin memverifikasi pengguna ini? Pengguna akan menerima notifikasi email dan dapat mengakses sistem.')
                    ->modalSubmitActionLabel('Ya, Verifikasi')
                    ->action(function (User $record): void {
                        // Update status verifikasi
                        $record->admin_verified = true;

                        // Jika email belum diverifikasi, verifikasi sekarang
                        if (!$record->hasVerifiedEmail()) {
                            $record->email_verified_at = Carbon::now();
                        }

                        $record->save();

                        // Kirim notifikasi email ke pengguna
                        $record->notify(new UserVerifiedByAdmin());

                        // Dapatkan admin yang melakukan verifikasi
                        $admin = Auth::user();

                        // Kirim notifikasi popup ke admin saat ini
                        Notification::make()
                            ->title('Pengguna Berhasil Diverifikasi')
                            ->success()
                            ->body("Pengguna {$record->name} telah berhasil diverifikasi dan notifikasi telah dikirim ke email mereka.")
                            ->icon('heroicon-o-check-circle')
                            ->send();

                        // Kirim notifikasi database ke semua admin lain
                        $adminUsers = User::whereHas('roles', fn ($query) => 
                        $query->whereIn('name', ['super_admin', 'admin'])
                        )->get();

                        foreach ($adminUsers as $otherAdmin) {
                            Notification::make()
                                ->title('Pengguna Diverifikasi')
                                ->success()
                                ->body("Pengguna {$record->name} telah diverifikasi oleh {$admin->name}.")
                                ->icon('heroicon-o-check-circle')
                                ->sendToDatabase($otherAdmin);
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('verifySelected')
                    ->label('Verifikasi Terpilih')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->action(function (\Illuminate\Database\Eloquent\Collection $records): void {
                        $records->each(function (User $record): void {
                            $record->admin_verified = true;

                            if (!$record->hasVerifiedEmail()) {
                                $record->email_verified_at = Carbon::now();
                            }

                            $record->save();

                            // Kirim notifikasi ke pengguna (optional)
                            // Implementasi notifikasi bisa ditambahkan di sini
                        });
                    }),
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
