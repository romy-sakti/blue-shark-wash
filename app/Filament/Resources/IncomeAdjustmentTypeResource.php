<?php

namespace App\Filament\Resources;

use App\Enums\AdjustmentDirection;
use App\Filament\Resources\IncomeAdjustmentTypeResource\Pages;
use App\Models\IncomeAdjustmentType;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class IncomeAdjustmentTypeResource extends Resource
{
    protected static ?string $model = IncomeAdjustmentType::class;

    protected static ?string $navigationIcon = 'tabler-adjustments';

    protected static ?string $navigationGroup = 'Master Data';

    protected static ?string $navigationLabel = 'Jenis Penyesuaian';

    protected static ?string $modelLabel = 'Jenis penyesuaian';

    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')->label('Nama')->required(),
            Forms\Components\Select::make('direction')
                ->label('Arah')
                ->options(AdjustmentDirection::class)
                ->required(),
            Forms\Components\Toggle::make('is_active')->label('Aktif')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable(),
                Tables\Columns\TextColumn::make('direction')->badge(),
                Tables\Columns\IconColumn::make('is_active')->boolean()->label('Aktif'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageIncomeAdjustmentTypes::route('/'),
        ];
    }
}
