<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WorkerRateResource\Pages;
use App\Filament\Support\MoneyInput;
use App\Models\Service;
use App\Models\WorkerRate;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class WorkerRateResource extends Resource
{
    protected static ?string $model = WorkerRate::class;

    protected static ?string $navigationIcon = 'tabler-user-dollar';

    protected static ?string $navigationGroup = 'Master Data';

    protected static ?string $navigationLabel = 'Tarif Pekerja';

    protected static ?string $modelLabel = 'Tarif pekerja';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('vehicle_type_id')
                ->label('Jenis kendaraan')
                ->relationship('vehicleType', 'name')
                ->required()
                ->live(),
            Forms\Components\Select::make('service_id')
                ->label('Layanan (opsional)')
                ->helperText('Kosongkan jika tarif berlaku untuk semua layanan jenis kendaraan ini.')
                ->options(fn (Forms\Get $get) => Service::query()
                    ->when($get('vehicle_type_id'), fn ($q, $id) => $q->where('vehicle_type_id', $id))
                    ->pluck('name', 'id'))
                ->searchable()
                ->nullable()
                ->dehydrateStateUsing(fn ($state) => $state ?: null),
            MoneyInput::make('rate')->label('Tarif / unit')->required(),
            Forms\Components\DatePicker::make('effective_from')
                ->label('Berlaku mulai')
                ->native(false)
                ->required()
                ->default(now()),
            Forms\Components\Textarea::make('notes')->label('Catatan')->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('vehicleType.name')->label('Kendaraan')->searchable(),
                Tables\Columns\TextColumn::make('service.name')->label('Layanan')->placeholder('Semua layanan'),
                Tables\Columns\TextColumn::make('rate')->label('Tarif')->alignEnd()->formatStateUsing(fn ($state) => rupiah($state)),
                Tables\Columns\TextColumn::make('effective_from')->label('Berlaku mulai')->date('d M Y')->sortable(),
                Tables\Columns\TextColumn::make('creator.name')->label('Dicatat oleh')->toggleable(),
            ])
            ->defaultSort('effective_from', 'desc')
            ->actions([
                Tables\Actions\EditAction::make()
                    ->mutateFormDataUsing(function (array $data) {
                        $data['rate'] = parse_rupiah($data['rate'] ?? 0);

                        return $data;
                    }),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageWorkerRates::route('/'),
        ];
    }
}
