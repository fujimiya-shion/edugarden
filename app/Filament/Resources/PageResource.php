<?php

namespace App\Filament\Resources;

use BackedEnum;
use App\Filament\Resources\PageResource\Pages;
use App\Models\Page;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class PageResource extends Resource
{
    protected static ?string $model = Page::class;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-document-chart-bar';

    // Việt hóa tiêu đề hiển thị ở sidebar
    protected static ?string $navigationLabel = 'Quản lý nội dung';

    // Label cho từng bản ghi (ví dụ: "Trang")
    public static function getModelLabel(): string
    {
        return 'trang';
    }

    public static function getPluralModelLabel(): string
    {
        return 'các trang';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->label('Tiêu đề')
                    ->required()
                    ->maxLength(255)
                    ->live(true)
                    ->afterStateUpdated(function ($state, $set, $get, $record) {
                        $set('slug', Str::slug($state));
                    })
                    ->columnSpan(['lg' => 2]),

                Forms\Components\TextInput::make('slug')
                    ->label('URL')
                    ->required()
                    ->maxLength(255)
                    ->columnSpan(['lg' => 2]),
                Forms\Components\RichEditor::make('summary')
                    ->label('Mô tả ngắn')
                    ->columnSpanFull(),

                Forms\Components\RichEditor::make('content')
                    ->label('Nội dung chính')
                    ->columnSpan(['lg' => 2]),

                Forms\Components\FileUpload::make('image')
                    ->label('Ảnh')
                    ->disk('public')
                    ->columnSpan(['lg' => 2]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')->label('Tiêu đề'),
                Tables\Columns\TextColumn::make('slug')->label('URL'),
                Tables\Columns\TextColumn::make('updated_at')->label('Cập nhật')->dateTime('d/m/Y H:i'),
                Tables\Columns\ImageColumn::make('image')->label('Ảnh'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('Chỉnh sửa'),
                Tables\Actions\DeleteAction::make()->label('Xoá'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->label('Xóa hàng loạt'),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPages::route('/'),
            'create' => Pages\CreatePage::route('/tao-moi'),
            'edit' => Pages\EditPage::route('/{record}/sua'),
        ];
    }
}
