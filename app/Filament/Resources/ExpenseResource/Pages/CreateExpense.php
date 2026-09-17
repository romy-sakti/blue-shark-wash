<?php

namespace App\Filament\Resources\ExpenseResource\Pages;

use App\Filament\Resources\ExpenseResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Carbon;

class CreateExpense extends CreateRecord
{
    protected static string $resource = ExpenseResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id();
        $data['amount'] = parse_rupiah($data['amount'] ?? 0);
        $data['attribution_month'] = isset($data['attribution_month'])
            ? Carbon::parse($data['attribution_month'])->startOfMonth()->toDateString()
            : Carbon::parse($data['expense_date'])->startOfMonth()->toDateString();

        return $data;
    }
}
