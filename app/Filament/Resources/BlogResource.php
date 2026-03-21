<?php

namespace App\Filament\Resources;

use BackedEnum;
use App\Filament\Resources\BlogResource\Pages;
use App\Models\Blog;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;

class BlogResource extends Resource
{
    protected static ?string $model = Blog::class;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $modelLabel = 'Cơ đốc giáo dục';
    protected static ?string $pluralModelLabel = 'Danh sách bài viết';
    protected static ?string $navigationLabel = 'Cơ đốc giáo dục';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->label('Tiêu đề')
                    ->afterStateUpdated(function ($state, callable $set) {
                        $set('slug', Str::slug($state));
                    })
                    ->columnSpan(['lg' => 2]),

                Textarea::make('description')
                    ->label('Mô tả ngắn')
                    ->rows(3)
                    ->maxLength(500)
                    ->columnSpan(['lg' => 2]),

                FileUpload::make('thumbnail')
                    ->label('Ảnh thumbnail')
                    ->image()
                    ->directory('blog-thumbnails'),

                Select::make('status')
                    ->required()
                    ->label('Trạng thái')
                    ->options([
                        '1' => 'Đang hoạt động',
                        '0' => 'Đã ẩn',
                    ])
                    ->default('1')
                    ->columnSpan(['lg' => 2]),

                RichEditor::make('content')
                    ->label('Nội dung')
                    ->required()
                    ->columnSpan(['lg' => 2]),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('thumbnail')
                    ->label('Thumbnail')
                    ->circular()
                    ->size(50)
                    ->getStateUsing(function ($record) {
                        $thumb = $record->thumbnail;

                        // Nếu là URL external
                        if (Str::startsWith($thumb, ['http://', 'https://'])) {
                            return $thumb; // Dùng nguyên URL
                        }

                        // Nếu là ảnh local
                        return asset('storage/' . $thumb);
                    }),

                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('slug')
                    ->copyable(),

                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        '0' => 'Đã ẩn',
                        '1'=> 'Đang hoạt động',
                    })
                    ->color(fn (string $state): string => match ($state) {
                        '0' => 'gray',
                        '1' => 'success',
                        default => 'secondary',
                    }),

                TextColumn::make('updated_at')
                    ->label('Updated')
                    ->since(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        '0' => 'Đã ẩn',
                        '1' => 'Đang hoạt động',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withoutGlobalScopes([
            SoftDeletingScope::class,
        ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBlogs::route('/'),
            'create' => Pages\CreateBlog::route('/create'),
            'edit' => Pages\EditBlog::route('/{record}/edit'),
        ];
    }
}
