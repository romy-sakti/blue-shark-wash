<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ServiceResource\Pages;
use App\Filament\Support\MoneyInput;
use App\Models\Service;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ServiceResource extends Resource
{
    protected static ?string $model = Service::class;

    protected static ?string $navigationIcon = 'tabler-sparkles';

    protected static ?string $navigationGroup = 'Master Data';

    protected static ?string $navigationLabel = 'Layanan';

    protected static ?string $modelLabel = 'Layanan';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('vehicle_type_id')
                ->label('Jenis kendaraan')
                ->relationship('vehicleType', 'name')
                ->required()
                ->searchable()
                ->preload(),
            Forms\Components\TextInput::make('name')->label('Nama layanan')->required(),
            MoneyInput::make('normal_price')
                ->label('Harga normal')
                ->required()
                ->helperText('Harga paket. Pembayaran lebih/kurang tidak mengubah nilai ini.'),
            MoneyInput::make('worker_cost')
                ->label('Biaya pekerja / unit')
                ->required(),
            Forms\Components\TextInput::make('sort_order')->label('Urutan')->numeric()->default(0),
            Forms\Components\Toggle::make('is_active')->label('Aktif')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('vehicleType.name')->label('Kendaraan')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('name')->label('Layanan')->searchable(),
                Tables\Columns\TextColumn::make('normal_price')->label('Harga')->alignEnd()->formatStateUsing(fn ($state) => rupiah($state)),
                Tables\Columns\TextColumn::make('worker_cost')->label('Pekerja')->alignEnd()->formatStateUsing(fn ($state) => rupiah($state)),
                Tables\Columns\IconColumn::make('is_active')->boolean()->label('Aktif'),
            ])
            ->defaultSort('vehicle_type_id')
            ->actions([
                Tables\Actions\EditAction::make()
                    ->mutateFormDataUsing(function (array $data) {
                        $data['normal_price'] = parse_rupiah($data['normal_price'] ?? 0);
                        $data['worker_cost'] = parse_rupiah($data['worker_cost'] ?? 0);

                        return $data;
                    }),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageServices::route('/'),
        ];
    }
}
