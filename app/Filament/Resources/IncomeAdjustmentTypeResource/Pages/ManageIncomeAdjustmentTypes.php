<?php

namespace App\Filament\Resources\IncomeAdjustmentTypeResource\Pages;

use App\Filament\Resources\IncomeAdjustmentTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageIncomeAdjustmentTypes extends ManageRecords
{
    protected static string $resource = IncomeAdjustmentTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
