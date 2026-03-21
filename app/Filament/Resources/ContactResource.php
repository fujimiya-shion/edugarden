<?php

namespace App\Filament\Resources;

use BackedEnum;
use App\Filament\Resources\ContactResource\Pages;
use App\Models\Contact;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class ContactResource extends Resource
{
    protected static ?string $model = Contact::class;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-device-phone-mobile';
    protected static ?string $navigationLabel = 'Quản lý liên hệ';
    protected static ?string $pluralLabel = 'Danh sách liên hệ';
    protected static ?string $label = 'Liên hệ';

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Forms\Components\TextInput::make('name')
                ->label('Họ và tên')
                ->required(),

            Forms\Components\TextInput::make('email')
                ->label('Email')
                ->email()
                ->required(),

            Forms\Components\TextInput::make('phone')
                ->label('Số điện thoại'),

            Forms\Components\TextInput::make('subject')
                ->label('Chủ đề'),

            Forms\Components\Textarea::make('message')
                ->label('Nội dung')
                ->rows(5)
                ->required()
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Họ và tên')->searchable(),
                Tables\Columns\TextColumn::make('email')->label('Email')->searchable(),
                Tables\Columns\TextColumn::make('phone')->label('Số điện thoại')->searchable(),
                Tables\Columns\TextColumn::make('subject')->label('Chủ đề')->limit(30),
                Tables\Columns\TextColumn::make('created_at')->label('Ngày gửi')->dateTime('d/m/Y H:i'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->label('Xem'),
                Tables\Actions\EditAction::make()->label('Chỉnh sửa'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->label('Xóa đã chọn'),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function canCreate(): bool {
        return false;
    }

    public static function canEdit(Model $record): bool {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListContacts::route('/'),
            'create' => Pages\CreateContact::route('/create'),
            'edit' => Pages\EditContact::route('/{record}/edit'),
        ];
    }
}
