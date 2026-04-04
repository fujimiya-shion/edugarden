<?php

namespace App\Filament\Resources;

use BackedEnum;
use App\Filament\Resources\BookResource\Pages;
use App\Models\Book;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class BookResource extends Resource
{
    protected static ?string $model = Book::class;

    protected static ?string $modelLabel = 'Sản phẩm';
    protected static ?string $pluralModelLabel = 'Danh sách sản phẩm';
    protected static ?string $navigationLabel = 'Quản lý sản phẩm';
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-book-open';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->label('Tiêu đề')
                    ->maxLength(255),

                Forms\Components\Select::make('category_id')
                    ->label('Danh mục')
                    ->relationship('category', 'name')
                    ->preload()
                    ->searchable()
                    ->nullable()
                    ->placeholder('- Không chọn -'),

                Forms\Components\FileUpload::make('thumbnail')
                    ->image()
                    ->disk('public')
                    ->label('Ảnh bìa')
                    ->directory('books/thumbnails'),

                Forms\Components\RichEditor::make('short_description')
                    ->label('Mô tả ngắn')
                    ->columnSpan(['lg' => 2])
                    ->required(),

                Forms\Components\RichEditor::make('description')
                    ->label('Mô tả')
                    ->columnSpan(['lg' => 2])
                    ->required(),

                Forms\Components\TextInput::make('price')
                    ->numeric()
                    ->label('Giá')
                    ->step(0.01)
                    ->required(),

                Forms\Components\TextInput::make('rating')
                    ->numeric()
                    ->readOnly()
                    ->default(5)
                    ->label('Đánh giá')
                    ->minValue(0)
                    ->maxValue(5)
                    ->visibleOn(['edit', 'view'])
                    ->step(0.1),

                Forms\Components\TextInput::make('buy_quantity')
                    ->numeric()
                    ->readOnly()
                    ->default(0)
                    ->visibleOn(['edit', 'view'])
                    ->minValue(0),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('thumbnail')
                    ->label('Bìa sách')
                    ->disk('public')
                    ->circular(),

                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('price')
                    ->money('VND', locale: 'vi')
                    ->sortable(),

                Tables\Columns\TextColumn::make('rating')
                    ->sortable(),

                Tables\Columns\TextColumn::make('buy_quantity')
                    ->label('Sold')
                    ->sortable(),
            ])
            ->filters([])
            ->actions([
                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBooks::route('/'),
            'create' => Pages\CreateBook::route('/create'),
            'edit' => Pages\EditBook::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder {
        return parent::getEloquentQuery()->orderBy('created_at', 'desc');
    }
}
