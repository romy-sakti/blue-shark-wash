<?php

namespace App\Filament\Resources\ExpenseResource\Pages;

use App\Filament\Resources\ExpenseResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Carbon;

class EditExpense extends EditRecord
{
    protected static string $resource = ExpenseResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['amount'] = format_angka($data['amount'] ?? 0);

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['amount'] = parse_rupiah($data['amount'] ?? 0);
        $data['attribution_month'] = isset($data['attribution_month'])
            ? Carbon::parse($data['attribution_month'])->startOfMonth()->toDateString()
            : Carbon::parse($data['expense_date'])->startOfMonth()->toDateString();

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
