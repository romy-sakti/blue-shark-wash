<?php

namespace App\Filament\Resources\ServiceResource\Pages;

use App\Filament\Resources\ServiceResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageServices extends ManageRecords
{
    protected static string $resource = ServiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->mutateFormDataUsing(function (array $data) {
                    $data['normal_price'] = parse_rupiah($data['normal_price'] ?? 0);
                    $data['worker_cost'] = parse_rupiah($data['worker_cost'] ?? 0);

                    return $data;
                }),
        ];
    }
}
