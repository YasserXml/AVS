<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BisnisfolderResource\Pages;
use App\Filament\Resources\BisnisfolderResource\RelationManagers;
use App\Models\Bisnisfolder;
use Filament\Forms;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use illuminate\Support\Str;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\DB;

class BisnisfolderResource extends Resource
{
    protected static ?string $model = Bisnisfolder::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';

    protected static ?string $navigationGroup = 'Arsip';

    protected static ?string $navigationLabel = 'Divisi Bisnis & Marketing';

    public static function getPluralLabel(): ?string
    {
        if (request()->has('model_type') && !request()->has('collection')) {
            return str(request()->get('model_type'))->afterLast('\\')->title();
        } else if (request()->has('model_type') && request()->has('collection')) {
            return str(request()->get('collection'))->title();
        } else {
            return ('Divisi Bisnis & Marketing');
        }
    }

    public static function getSlug(): string
    {
        return 'arsip/bisnis';
    }

    public static function getNavigationSort(): ?int
    {
        return 19;
    }

    protected static ?string $slug = 'bisnis';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Hidden::make('user_id')
                    ->default(filament()->auth()->id()),
                Hidden::make('user_type')
                    ->default(get_class(filament()->auth()->user())),


                TextInput::make('name')
                    ->label('Nama Folder')
                    ->columnSpanFull()
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (Set $set, Get $get) {
                        $set('collection', Str::slug($get('name')));
                    })
                    ->required()
                    ->maxLength(255),
                // Tambahkan select untuk kategori
                Select::make('kategori_id')
                    ->label('Kategori')
                    ->relationship('kategori', 'nama_kategori')
                    ->searchable()
                    ->preload()
                    ->reactive()
                    ->live()
                    ->createOptionForm([
                        TextInput::make('nama_kategori')
                            ->label('Nama Kategori')
                            ->required()
                            ->maxLength(255)
                            ->unique()
                            ->placeholder('Masukkan nama kategori baru')
                    ])
                    ->createOptionAction(function (Action $action) {
                        return $action
                            ->modalHeading('Buat Kategori Baru')
                            ->modalSubmitActionLabel('Buat')
                            ->modalCancelActionLabel('Batal');
                    })
                    ->columnSpanFull(),
                TextInput::make('collection')
                    ->label('Koleksi')
                    ->columnSpanFull()
                    ->unique()
                    ->hidden()
                    ->required()
                    ->maxLength(255),
                Textarea::make('description')
                    ->label('Deskripsi')
                    ->columnSpanFull()
                    ->maxLength(255),
                ColorPicker::make('color')
                    ->label('Warna Folder')
                    ->default('#ffab09'),
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
                    // Hanya tampilkan folder root (tanpa parent_id)
                    // dan folder yang bukan subfolder dari folder lain
                    $query->where(function ($q) {
                        $q->where('model_id', null)
                            ->where('collection', null)
                            ->orWhere('model_type', null);
                    })
                        // KUNCI UTAMA: Hanya tampilkan folder yang tidak memiliki parent
                        ->whereNull('parent_id');
                }
                $query->with(['kategori:id,nama_kategori'])
                    ->leftJoin('kategoribisnis', 'bisnisfolders.kategori_id', '=', 'kategoribisnis.id')
                    ->addSelect([
                        'bisnisfolders.*',
                        DB::raw("CASE WHEN bisnisfolders.kategori_id IS NULL THEN 'zzz_tanpa_kategori' ELSE kategoribisnis.nama_kategori END as kategori_sort")
                    ])
                    ->orderBy('kategori_sort')
                    ->orderBy('bisnisfolders.name');
            })
            ->content(function () {
                return view('folders.Bisnis.folder');
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
            ->emptyStateHeading('Belum Ada folder')
            ->emptyStateIcon('heroicon-o-folder')
            ->emptyStateDescription('Buat folder pertama anda.')
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->label('Buat Folder')
                    ->icon('heroicon-m-folder-plus'),
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
            'index' => Pages\ListBisnisfolders::route('/'),
            // 'create' => Pages\CreateBisnisfolder::route('/create'),
            // 'edit' => Pages\EditBisnisfolder::route('/{record}/edit'),
        ];
    }
}
