<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DirektoratfolderResource\Pages;
use App\Filament\Resources\DirektoratfolderResource\RelationManagers;
use App\Models\Direktoratfolder;
use Filament\Forms;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use illuminate\Support\Str;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use TomatoPHP\FilamentIcons\Components\IconPicker;

class DirektoratfolderResource extends Resource
{
    protected static ?string $model = Direktoratfolder::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';

    protected static ?string $navigationGroup = 'Arsip';

    protected static ?string $navigationLabel = 'Direktorat';

    public static function getPluralLabel(): ?string
    {
        if (request()->has('model_type') && !request()->has('collection')) {
            return str(request()->get('model_type'))->afterLast('\\')->title();
        } else if (request()->has('model_type') && request()->has('collection')) {
            return str(request()->get('collection'))->title();
        } else {
            return ('Direktorat');
        }
    }

    public static function getSlug(): string
    {
        return 'arsip/direktorat';
    }

    public static function getNavigationSort(): ?int
    {
        return 7;
    }

    protected static ?string $slug = 'direktorat';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Hidden::make('user_id')
                    ->default(filament()->auth()->id()),
                Hidden::make('user_type')
                    ->default(get_class(filament()->auth()->user())),
                TextInput::make('name')
                    ->label('Nama')
                    ->columnSpanFull()
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (Set $set, Get $get) {
                        $set('collection', Str::slug($get('name')));
                    })
                    ->required()
                    ->maxLength(255),
                TextInput::make('collection')
                    ->label('Koleksi')
                    ->columnSpanFull()
                    ->unique()
                    ->required()
                    ->maxLength(255),
                Textarea::make('description')
                    ->label('Deskripsi')
                    ->columnSpanFull()
                    ->maxLength(255),
                ColorPicker::make('color')
                    ->label('Warna'),
                Toggle::make('is_protected')
                    ->label('Dilindungi Password')
                    ->live()
                    ->columnSpanFull(),
                TextInput::make('password')
                    ->label('Password')
                    ->hidden(fn(Forms\Get $get) => !$get('is_protected'))
                    ->password()
                    ->revealable()
                    ->required(fn(Forms\Get $get) => $get('is_protected'))
                    ->maxLength(255),
                TextInput::make('password_confirmation')
                    ->label('Konfirmasi Password')
                    ->hidden(fn(Forms\Get $get) => !$get('is_protected'))
                    ->password()
                    ->required(fn(Forms\Get $get) => $get('is_protected'))
                    ->revealable()
                    ->maxLength(255)
                    ->same('password'),
            ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
             ->modifyQueryUsing(function (Builder $query) {
                if (request()->has('model_type') && !request()->has('collection')) {
                    $query->where('model_type', request()->get('model_type'))
                        ->where('model_id', null)
                        ->whereNotNull('collection');
                } else if (request()->has('model_type') && request()->has('collection')) {
                    $query->where('model_type', request()->get('model_type'))
                        ->whereNotNull('model_id')
                        ->where('collection', request()->get('collection'));
                } else {
                    // PERBAIKAN: Hanya tampilkan folder root (tanpa parent_id)
                    // dan folder yang bukan subfolder dari folder lain
                    $query->where(function ($q) {
                        $q->where('model_id', null)
                          ->where('collection', null)
                          ->orWhere('model_type', null);
                    })
                    // KUNCI UTAMA: Hanya tampilkan folder yang tidak memiliki parent
                    ->whereNull('parent_id');
                }
            })
            ->content(function () {
                return view('folders.folder');
            })
            ->columns([
                Stack::make([
                    TextColumn::make('name')
                        ->label('Nama')
                        ->searchable(),
                    TextColumn::make('description')
                        ->label('Deskripsi')
                        ->searchable(),
                    TextColumn::make('icon')
                        ->label('Ikon')
                        ->searchable(),
                    TextColumn::make('color')
                        ->label('Warna')
                        ->searchable(),
                    IconColumn::make('is_protected')
                        ->label('Dilindungi')
                        ->boolean(),
                    TextColumn::make('created_at')
                        ->label('Dibuat pada')
                        ->dateTime()
                        ->toggleable(isToggledHiddenByDefault: true),
                    TextColumn::make('updated_at')
                        ->label('Diubah pada')
                        ->dateTime()
                        ->toggleable(isToggledHiddenByDefault: true),
                ])
            ])
            ->defaultPaginationPageOption(12)
            ->paginationPageOptions([
                "12",
                "24",
                "48",
                "96",
            ])
            ->contentGrid([
                'md' => 3,
                'xl' => 4,
            ])
            ->filters([])
            ->actions([])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->label('Buat Folder')
                    ->icon('heroicon-m-folder-plus'),
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
            'index' => Pages\ListDirektoratfolders::route('/'),
            // 'create' => Pages\CreateDirektoratfolder::route('/create'),
            // 'view' => Pages\ViewDirektoratfolder::route('/{record}'),s
            // 'edit' => Pages\EditDirektoratfolder::route('/{record}/edit'),
        ];
    }
}
