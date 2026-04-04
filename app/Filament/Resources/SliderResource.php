<?php

namespace App\Filament\Resources;

use BackedEnum;
use App\Filament\Resources\SliderResource\Pages;
use App\Models\Slider;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class SliderResource extends Resource
{
    protected static ?string $model = Slider::class;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-photo';
    protected static ?string $navigationLabel = 'Slider';
    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\FileUpload::make('url')
                    ->label('Ảnh')
                    ->disk('public')
                    ->directory('sliders')
                    ->image()
                    ->imageEditor()
                    ->imageEditorAspectRatios([
                        '21:9',
                    ])
                    ->required(),
                Forms\Components\TextInput::make('href')
                    ->label('Link liên kết'),
                Forms\Components\TextInput::make('order')
                    ->label('Order')
                    ->default(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('url')
                    ->label('Ảnh')
                    ->disk('public'),
                    
                Tables\Columns\TextColumn::make('href')
                    ->label('Link liên kết'),
                Tables\Columns\TextColumn::make('order')
                    ->label('Order'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Actions\EditAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('order', 'asc');
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
            'index' => Pages\ListSliders::route('/'),
            'create' => Pages\CreateSlider::route('/create'),
            'edit' => Pages\EditSlider::route('/{record}/edit'),
        ];
    }
}
