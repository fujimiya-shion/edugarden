<?php

namespace App\Filament\Resources;

use BackedEnum;
use App\Enums\DeliveryStatuses;
use App\Enums\PaymentMethods;
use App\Enums\PaymentStatuses;
use App\Filament\RelationManagers\OrderItemsRelationManager;
use App\Filament\Resources\OrderResource\Pages;
use App\Models\Order;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $modelLabel = 'Đơn hàng';
    protected static ?string $pluralModelLabel = 'Danh sách đơn hàng';
    protected static ?string $navigationLabel = 'Quản lý đơn hàng';
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-shopping-cart';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\Section::make([
                    Forms\Components\Placeholder::make('user_id')
                        ->label('Khách hàng')
                        ->content(fn ($record) => $record?->userAddress?->name),
    
                    Forms\Components\Placeholder::make('user_address_id')
                        ->label('Địa chỉ giao hàng')
                        ->content(fn ($record) => implode(', ', array_filter([
                            $record?->userAddress?->address_detail,
                            $record?->userAddress?->ward_name,
                            $record?->userAddress?->district_name,
                            $record?->userAddress?->city_name,
                        ]))),
                ])
                ->columns(2),

                Forms\Components\TextInput::make('total')
                    ->label('Tổng tiền')
                    ->numeric()
                    ->readOnly()
                    ->required()
                    ->prefix('₫'),

                Forms\Components\Select::make('payment_method')
                    ->label('Phương thức thanh toán')
                    ->options([
                        PaymentMethods::COD => PaymentMethods::label(PaymentMethods::COD),
                        PaymentMethods::ONLINE => PaymentMethods::label(PaymentMethods::ONLINE),
                    ])
                    ->required(),

                Forms\Components\Select::make('payment_status')
                    ->label('Trạng thái thanh toán')
                    ->options([
                        PaymentStatuses::PAID => PaymentStatuses::label(PaymentStatuses::PAID),
                        PaymentStatuses::NOT_PAID => PaymentStatuses::label(PaymentStatuses::NOT_PAID),
                    ])
                    ->required(),

                Forms\Components\Select::make('delivery_status')
                    ->label('Trạng thái giao hàng')
                    ->options([
                        DeliveryStatuses::PENDING => DeliveryStatuses::label(DeliveryStatuses::PENDING),
                        DeliveryStatuses::CONFIRMED => DeliveryStatuses::label(DeliveryStatuses::CONFIRMED),
                        DeliveryStatuses::DELIVERED => DeliveryStatuses::label(DeliveryStatuses::DELIVERED),
                        DeliveryStatuses::CANCELLED => DeliveryStatuses::label(DeliveryStatuses::CANCELLED),
                    ])
                    ->required(),

                Forms\Components\Textarea::make('special_request')
                    ->label('Ghi chú khách hàng')
                    ->columnSpan(['lg' => 2]),

                

                Forms\Components\Toggle::make('is_export_invoice')
                    ->label('Khách hàng mong muốn xuất hoá đơn')
                    ->disabled(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->label('Mã đơn')->sortable()->searchable()->formatStateUsing(fn ($state) => "EORD-" . str_pad($state, 3, '0', STR_PAD_LEFT)),
                TextColumn::make('userAddress.name')->label('Khách hàng')->sortable()->searchable(),
                TextColumn::make('total')->label('Tổng tiền')->money('VND', true),
                BadgeColumn::make('payment_method')->label('Phương thức')
                    ->colors([
                        'info' => PaymentMethods::COD,
                        'success' => PaymentMethods::ONLINE,
                    ])
                    ->formatStateUsing(fn ($state) => PaymentMethods::label($state)),
                BadgeColumn::make('payment_status')
                    ->label('Thanh toán')
                    ->colors([
                        'success' => PaymentStatuses::PAID,
                        'danger' => PaymentStatuses::NOT_PAID,
                    ])
                    ->formatStateUsing(fn ($state) => PaymentStatuses::label($state)),
                BadgeColumn::make('delivery_status')
                    ->label('Giao hàng')
                    ->colors([
                        'warning' => DeliveryStatuses::PENDING,
                        'info' => DeliveryStatuses::CONFIRMED,
                        'success' => DeliveryStatuses::DELIVERED,
                        'danger' => DeliveryStatuses::CANCELLED,
                    ])
                    ->formatStateUsing(fn ($state) => DeliveryStatuses::label($state)),
                TextColumn::make('created_at')->label('Ngày đặt hàng')->dateTime('d/m/Y H:i:m'),
            ])
            ->filters([
                Tables\Filters\MultiSelectFilter::make('payment_method')
                    ->label('Phương thức thanh toán')
                    ->options([
                        PaymentMethods::COD => PaymentMethods::label(PaymentMethods::COD),
                        PaymentMethods::ONLINE => PaymentMethods::label(PaymentMethods::ONLINE),
                    ]),
                Tables\Filters\MultiSelectFilter::make('payment_status')
                    ->label('Trạng thái thanh toán')
                    ->options([
                        PaymentStatuses::PAID => PaymentStatuses::label(PaymentStatuses::PAID),
                        PaymentStatuses::NOT_PAID => PaymentStatuses::label(PaymentStatuses::NOT_PAID),
                    ]),
                Tables\Filters\MultiSelectFilter::make('delivery_status')
                    ->label('Trạng thái giao hàng')
                    ->options([
                        DeliveryStatuses::PENDING => DeliveryStatuses::label(DeliveryStatuses::PENDING),
                        DeliveryStatuses::CONFIRMED => DeliveryStatuses::label(DeliveryStatuses::CONFIRMED),
                        DeliveryStatuses::DELIVERED => DeliveryStatuses::label(DeliveryStatuses::DELIVERED),
                        DeliveryStatuses::CANCELLED => DeliveryStatuses::label(DeliveryStatuses::CANCELLED),
                    ]),
            ])
            ->actions([
                Actions\EditAction::make()->label('Chỉnh sửa'),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make()->label('Xóa đã chọn'),
                ]),
            ])
            ->defaultSort('id', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            OrderItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
