<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VehicleTypeResource\Pages;
use App\Models\VehicleType;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class VehicleTypeResource extends Resource
{
    protected static ?string $model = VehicleType::class;

    protected static ?string $navigationIcon = 'tabler-motorbike';

    protected static ?string $navigationGroup = 'Master Data';

    protected static ?string $navigationLabel = 'Jenis Kendaraan';

    protected static ?string $modelLabel = 'Jenis kendaraan';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')->label('Nama')->required()->maxLength(100),
            Forms\Components\TextInput::make('sort_order')->label('Urutan')->numeric()->default(0),
            Forms\Components\Toggle::make('is_active')->label('Aktif')->default(true),
            Forms\Components\Toggle::make('appears_on_form')
                ->label('Tampil di form pembukuan')
                ->helperText('Aktifkan hanya untuk paket tetap Motor dan Mobil. Jasa lepas dicatat di layanan lainnya pada form pembukuan.')
                ->default(true),
            Forms\Components\Textarea::make('description')->label('Deskripsi')->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Nama')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('services_count')->counts('services')->label('Layanan'),
                Tables\Columns\IconColumn::make('is_active')->boolean()->label('Aktif'),
                Tables\Columns\IconColumn::make('appears_on_form')->boolean()->label('Form paket'),
                Tables\Columns\TextColumn::make('sort_order')->label('Urutan')->sortable(),
            ])
            ->defaultSort('sort_order')
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageVehicleTypes::route('/'),
        ];
    }
}
