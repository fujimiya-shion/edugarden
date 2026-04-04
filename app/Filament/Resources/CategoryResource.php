<?php

namespace App\Filament\Resources;

use BackedEnum;
use App\Filament\Resources\CategoryResource\Pages;
use App\Models\Category;
use Filament\Actions;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static ?string $modelLabel = 'Danh mục';
    protected static ?string $pluralModelLabel = 'Danh sách danh mục';
    protected static ?string $navigationLabel = 'Quản lý danh mục';
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-tag';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Thông tin danh mục')
                    ->schema([
                        TextInput::make('name')
                            ->label('Tên danh mục')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),

                        // slug tạo tự động trong model -> chỉ hiển thị để tham khảo
                        TextInput::make('slug')
                            ->label('Slug')
                            ->disabled()
                            ->dehydrated(false) // không gửi về server vì model đã tự set
                            ->helperText('Tự động sinh theo tên khi tạo/cập nhật.'),

                        Select::make('parent_id')
                            ->label('Danh mục cha')
                            ->searchable()
                            ->preload()
                            ->options(fn() => Category::query()
                                ->orderBy('name')
                                ->pluck('name', 'id'))
                            ->native(false)
                            ->placeholder('— Không có —')
                            ->nullable(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('name')
                    ->label('Tên')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('slug')
                    ->label('Slug')
                    ->toggleable(),

                TextColumn::make('parent_id')
                    ->label('Danh mục cha')
                    ->formatStateUsing(function ($state) {
                        if (!$state)
                            return '—';
                        return Category::query()->whereKey($state)->value('name') ?? '—';
                    })
                    ->sortable()
                    ->searchable(),

                TextColumn::make('created_at')
                    ->label('Tạo lúc')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Cập nhật')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('parent_id')
                    ->label('Lọc theo danh mục cha')
                    ->options(fn() => Category::query()
                        ->orderBy('name')
                        ->pluck('name', 'id'))
                    ->placeholder('Tất cả'),

                SelectFilter::make('has_parent')
                    ->label('Có danh mục cha?')
                    ->options([
                        'no' => 'Không có cha',
                        'yes' => 'Có cha',
                    ])
                    ->query(function (Builder $query, array $data) {
                        return match ($data['value'] ?? null) {
                            'no' => $query->whereNull('parent_id'),
                            'yes' => $query->whereNotNull('parent_id'),
                            default => $query,
                        };
                    }),
            ])
            ->actions([
                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
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
            'index' => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'edit' => Pages\EditCategory::route('/{record}/edit'),
        ];
    }
}
