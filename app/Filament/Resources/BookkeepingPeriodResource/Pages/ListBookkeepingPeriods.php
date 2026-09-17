<?php

namespace App\Filament\Resources\BookkeepingPeriodResource\Pages;

use App\Filament\Resources\BookkeepingPeriodResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBookkeepingPeriods extends ListRecords
{
    protected static string $resource = BookkeepingPeriodResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Input pembukuan'),
        ];
    }
}
