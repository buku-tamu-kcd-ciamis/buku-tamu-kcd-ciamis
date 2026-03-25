<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $slug = 'product';
    protected static string|\UnitEnum|null $navigationGroup = 'Toko';
    protected static ?string $navigationLabel = 'Produk';
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedShoppingBag;
    protected static ?int $navigationSort = 2;
    protected static bool $shouldRegisterNavigation = false;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('General')
                    ->description('this is description')
                    ->schema([
                        TextInput::make('name')
                            ->required(),
                        TextInput::make('slug')
                            ->readOnly(),
                        TextInput::make('sku')
                            ->required(),
                        Textarea::make('excerpt')
                            ->autosize()
                            ->rows(10),
                        Textarea::make('body')
                            ->autosize()
                            ->rows(10),
                        Select::make('category_id')
                            ->required()
                            ->relationship('category', 'name'),
                    ])
                    ->aside(),
                Section::make('Pricing')
                    ->description('this is description')
                    ->schema([
                        TextInput::make('price')
                            ->numeric()
                            ->minValue(0),
                        TextInput::make('price_sale')
                            ->numeric()
                            ->minValue(0),
                        Checkbox::make('stock_status'),
                        TextInput::make('manage_stock')
                            ->numeric()
                            ->minValue(0),
                    ])
                    ->aside()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('featured_image')
                    ->defaultImageUrl('https://www.psykososialberedskap.no/wp-content/themes/rvts_psb_sage-2.0/resources/assets/images/default-placeholder.png'),
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('category.name')->searchable()->sortable(),
                TextColumn::make('price')->sortable()->money('idr'),
                TextColumn::make('status')
                    ->formatStateUsing(fn(string $state): string => Product::STATUSSES[$state]),
                TextColumn::make('manage_stock')
                    ->label('Stock')
            ])
            ->filters([
                //
            ])
            ->actions([])
            ->bulkActions([]);
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
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            // 'view' => Pages\ViewProduct::route('/{record}'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
