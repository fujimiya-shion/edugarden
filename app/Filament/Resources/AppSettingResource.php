<?php

namespace App\Filament\Resources;

use BackedEnum;
use App\Enums\ModuleTypes;
use App\Filament\Resources\AppSettingResource\Pages;
use App\Models\AppSetting;
use App\Models\Page;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class AppSettingResource extends Resource
{
    protected static ?string $model = AppSetting::class;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-cog-6-tooth';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\Toggle::make('is_active')
                    ->label('Sử dụng cấu hình này'),

                Forms\Components\Card::make()
                    ->schema([
                        Forms\Components\Repeater::make('sections')
                            ->label('Quản lý trang chủ')
                            ->columnSpanFull()
                            ->schema([
                                Forms\Components\TextInput::make('title')
                                    ->label('Tiêu đề')
                                    ->maxLength(255)
                                    ->columnSpanFull(),

                                Forms\Components\Select::make('module')
                                    ->label('Module')
                                    ->options(ModuleTypes::values())
                                    ->live(onBlur: true)
                                    ->required()
                                    ->columnSpanFull(),

                                Forms\Components\Select::make('record')
                                    ->label('Chọn trang')
                                    ->options(fn () => Page::pluck('title', 'id')->toArray())
                                    ->searchable()
                                    ->visible(fn ($get) => $get('module') == ModuleTypes::PAGE)
                                    ->required(fn ($get) => $get('module') == ModuleTypes::PAGE)
                                    ->columnSpanFull(),
                            ])
                            ->default([])
                            ->collapsible()
                            ->reorderable(),
                    ])
                    ->label('Quản lý Trang Chủ'),

                Forms\Components\Card::make()
                    ->schema([
                        Forms\Components\TextInput::make(' meta_title')
                            ->label('Meta Title')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('meta_description')
                            ->label('Meta Description')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('meta_keywords')
                            ->label('Meta Keywords')
                            ->maxLength(255),

                        Forms\Components\FileUpload::make('meta_image')
                            ->label('Meta Image')
                            ->disk('public'),

                        Forms\Components\TextInput::make('meta_url')
                            ->label('Meta URL')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('meta_type')
                            ->label('Meta Type')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('meta_locale')
                            ->label('Meta Locale')
                            ->columnSpanFull()
                            ->maxLength(255),
                    ])
                    ->label('SEO Metadata'),
            ]);
    }


    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('meta_title')
                    ->label('Meta Title')
                    ->limit(50)
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('meta_description')
                    ->label('Meta Description')
                    ->limit(50)
                    ->searchable(),

                Tables\Columns\TextColumn::make('meta_keywords')
                    ->label('Meta Keywords')
                    ->limit(50)
                    ->searchable(),

                Tables\Columns\ImageColumn::make('meta_image')
                    ->label('Meta Image'),

                Tables\Columns\TextColumn::make('meta_url')
                    ->label('Meta URL')
                    ->limit(50)
                    ->sortable(),

                Tables\Columns\TextColumn::make('meta_type')
                    ->label('Meta Type')
                    ->limit(30),

                Tables\Columns\TextColumn::make('meta_locale')
                    ->label('Meta Locale')
                    ->limit(30),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime()
                    ->sortable(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Updated At')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                // Thêm filters nếu cần
            ])
            ->actions([
                Actions\ViewAction::make(),
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
            'index' => Pages\ListAppSettings::route('/'),
            'create' => Pages\CreateAppSetting::route('/create'),
            'edit' => Pages\EditAppSetting::route('/{record}/edit'),
        ];
    }
}
