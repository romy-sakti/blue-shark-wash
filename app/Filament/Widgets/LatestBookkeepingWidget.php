<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\BookkeepingPeriodResource;
use App\Models\BookkeepingPeriod;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestBookkeepingWidget extends BaseWidget
{
    protected static ?string $heading = 'Pembukuan terbaru';

    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(BookkeepingPeriod::query()->with('summary')->latest('period_date'))
            ->columns([
                Tables\Columns\TextColumn::make('period_date')->label('Tanggal')->date('d M Y'),
                Tables\Columns\TextColumn::make('status')->badge(),
                Tables\Columns\TextColumn::make('summary.total_units')->label('Unit'),
                Tables\Columns\TextColumn::make('summary.actual_revenue')->label('Omzet')->formatStateUsing(fn ($state) => rupiah($state)),
                Tables\Columns\TextColumn::make('summary.net_profit')->label('Laba')->formatStateUsing(fn ($state) => rupiah($state)),
            ])
            ->actions([
                Tables\Actions\Action::make('open')
                    ->label('Lihat')
                    ->url(fn (BookkeepingPeriod $record) => BookkeepingPeriodResource::getUrl('view', ['record' => $record])),
            ])
            ->paginated([5]);
    }
}
