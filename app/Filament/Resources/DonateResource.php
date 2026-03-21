<?php

namespace App\Filament\Resources;

use BackedEnum;
use App\Filament\Resources\DonateResource\Pages;
use App\Models\Donate;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class DonateResource extends Resource
{
    protected static ?string $model = Donate::class;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-currency-dollar';
    protected static ?string $navigationLabel = 'Quản lý dâng hiến';
    protected static ?string $modelLabel = 'Dâng hiến';
    protected static ?string $pluralModelLabel = 'Dâng hiến';

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Forms\Components\TextInput::make('name')->label('Họ và tên')->required(),
            Forms\Components\TextInput::make('email')->label('Email')->email(),
            Forms\Components\TextInput::make('phone')->label('Số điện thoại'),
            Forms\Components\TextInput::make('amount')->label('Số tiền')->numeric()->required(),
            Forms\Components\Textarea::make('note')->label('Lời nhắn'),
            Forms\Components\Toggle::make('is_received')->label('Đã nhận')->default(false),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('name')->label('Họ tên')->searchable(),
            Tables\Columns\TextColumn::make('phone')->label('SĐT'),
            Tables\Columns\TextColumn::make('amount')->label('Số tiền')->money('VND'),
            Tables\Columns\IconColumn::make('is_received')->boolean()->label('Đã nhận'),
            Tables\Columns\TextColumn::make('created_at')->label('Thời gian')->dateTime('d/m/Y H:i'),
        ])
        ->actions([
            Tables\Actions\EditAction::make(),
            Tables\Actions\DeleteAction::make(),
        ])
        ->bulkActions([
            Tables\Actions\DeleteBulkAction::make(),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDonates::route('/'),
            'create' => Pages\CreateDonate::route('/create'),
            'edit' => Pages\EditDonate::route('/{record}/edit'),
        ];
    }
}
