<?php

namespace App\Filament\Resources;

use BackedEnum;
use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Illuminate\Database\Eloquent\Builder;

class UserResource extends Resource
{
    protected static ?string $model = User::class;
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-user';
    protected static ?string $modelLabel = 'Tài khoản';
    protected static ?string $pluralModelLabel = 'Danh sách tài khoản';
    protected static ?string $navigationLabel = 'Quản lý tài khoản';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TextInput::make('name')
                    ->label('Họ tên')
                    ->required()
                    ->maxLength(255),

                TextInput::make('email')
                    ->label('Email')
                    ->required()
                    ->email()
                    ->unique(ignoreRecord: true),

                TextInput::make('phone')
                    ->label('Số điện thoại')
                    ->tel()
                    ->required()
                    ->maxLength(20),

                Select::make('church_id')
                    ->label('Hội thánh')
                    ->relationship('church', 'name')
                    ->searchable()
                    ->preload(),

                TextInput::make('password')
                    ->label('Mật khẩu')
                    ->password()
                    ->required(fn (string $context) => $context === 'create')
                    ->dehydrateStateUsing(fn ($state) => filled($state) ? bcrypt($state) : null)
                    ->dehydrated(fn ($state) => filled($state))
                    ->maxLength(255)
                    ->visibleOn(['create']),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Họ tên')->searchable(),
                TextColumn::make('email')->label('Email')->searchable(),
                TextColumn::make('phone')->label('Số điện thoại'),
                TextColumn::make('church.name')->label('Hội thánh')->sortable(),
                TextColumn::make('created_at')
                    ->label('Ngày tạo')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\MultiSelectFilter::make('church')
                    ->label('Hội thánh')
                    ->relationship('church', 'name'),      
            ])
            ->actions([
                Tables\Actions\Action::make('changePassword')
                    ->label('Đổi mật khẩu')
                    ->modalHeading('Đổi mật khẩu tài khoản')
                    ->modalDescription('Vui lòng nhập mật khẩu mới cho tài khoản')
                    ->form([
                        TextInput::make('password')
                            ->label('Mật khẩu mới')
                            ->password()
                            ->required()
                            ->maxLength(255),
                    ])
                    ->action(function (User $record, array $data): void {
                        $record->update([
                            'password' => bcrypt($data['password']),
                        ]);
                    })
                    ->color('warning')
                    ->icon('heroicon-s-lock-closed'),

                Tables\Actions\EditAction::make()->label('Sửa'),
                Tables\Actions\DeleteAction::make()->label('Xoá'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->label('Xoá đã chọn'),
                ]),
            ])
            ->defaultSort('id', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
