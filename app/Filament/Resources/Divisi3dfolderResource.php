<?php

namespace App\Filament\Resources;

use App\Filament\Resources\Divisi3dfolderResource\Pages;
use App\Filament\Resources\Divisi3dfolderResource\RelationManagers;
use App\Models\Divisi3dfolder;
use Filament\Forms;
use Filament\Forms\Components\Actions\Action as ActionsAction;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Forms\Get;
use illuminate\Support\Str;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\DB;

class Divisi3dfolderResource extends Resource
{
    protected static ?string $model = Divisi3dfolder::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';

    protected static ?string $navigationGroup = 'Arsip';

    protected static ?string $navigationLabel = 'Divisi 3D';

    public static function getPluralLabel(): ?string
    {
        if (request()->has('model_type') && !request()->has('collection')) {
            return str(request()->get('model_type'))->afterLast('\\')->title();
        } else if (request()->has('model_type') && request()->has('collection')) {
            return str(request()->get('collection'))->title();
        } else {
            return ('Divisi 3D');
        }
    }

    public static function getSlug(): string
    {
        return 'arsip/3d';
    }

    public static function getNavigationSort(): ?int
    {
        return 29;
    }

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
                    ->createOptionAction(function (ActionsAction $action) {
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

    // Update pada bagian table untuk menampilkan kategori
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
                    $query->where(function ($q) {
                        $q->where('model_id', null)
                            ->where('collection', null)
                            ->orWhere('model_type', null);
                    })
                        ->whereNull('parent_id');
                }
                // Optimasi query untuk pengelompokan kategori
                $query->with(['kategori:id,nama_kategori'])
                    ->leftJoin('kategori3ds', 'divisi3dfolders.kategori_id', '=', 'kategori3ds.id')
                    ->addSelect([
                        'divisi3dfolders.*',
                        DB::raw("CASE WHEN divisi3dfolders.kategori_id IS NULL THEN 'zzz_tanpa_kategori' ELSE kategori3ds.nama_kategori END as kategori_sort")
                    ])
                    ->orderBy('kategori_sort')
                    ->orderBy('divisi3dfolders.name');
            })
            ->content(function () {
                return view('folders.3D.folder');
            })
            // ... rest of the table configuration remains the same
            ->columns([
                Stack::make([
                    TextColumn::make('name')
                        ->label('Nama')
                        ->searchable(),
                    TextColumn::make('kategori.nama_kategori')
                        ->label('Kategori')
                        ->badge()
                        ->color('primary')
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
            ->filters([
                SelectFilter::make('kategori_id')
                    ->label('Filter berdasarkan Kategori')
                    ->relationship('kategori', 'nama_kategori')
                    ->searchable()
                    ->preload()
                    ->multiple()
                    ->placeholder('Pilih Kategori'),

                // Tambahkan filter untuk folder tanpa kategori
                Filter::make('tanpa_kategori')
                    ->label('Tanpa Kategori')
                    ->query(fn(Builder $query): Builder => $query->whereNull('kategori_id'))
                    ->toggle(),
            ])
            ->actions([
                // Actions existing...
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
            'index' => Pages\ListDivisi3dfolders::route('/'),
        ];
    }
}
