<?php

namespace App\Filament\Resources\WorkerRateResource\Pages;

use App\Filament\Resources\WorkerRateResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageWorkerRates extends ManageRecords
{
    protected static string $resource = WorkerRateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->mutateFormDataUsing(function (array $data) {
                    $data['created_by'] = auth()->id();
                    $data['rate'] = parse_rupiah($data['rate'] ?? 0);

                    return $data;
                }),
        ];
    }
}
