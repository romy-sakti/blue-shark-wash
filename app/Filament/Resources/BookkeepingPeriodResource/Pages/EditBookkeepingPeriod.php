<?php

namespace App\Filament\Resources\BookkeepingPeriodResource\Pages;

use App\Enums\ExpenseAllocation;
use App\Filament\Resources\BookkeepingPeriodResource;
use App\Models\BookkeepingPeriod;
use App\Services\BookkeepingPeriodService;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditBookkeepingPeriod extends EditRecord
{
    protected static string $resource = BookkeepingPeriodResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $this->record->load(['items', 'expenses']);

        $data['quantities'] = $this->record->items
            ->where('is_custom', false)
            ->whereNotNull('service_id')
            ->pluck('quantity', 'service_id')
            ->all();

        $data['extra_lines'] = $this->record->items
            ->where('is_custom', true)
            ->values()
            ->map(fn ($item) => [
                'id' => $item->id,
                'name' => $item->custom_name,
                'quantity' => $item->quantity,
                'amount' => format_angka($item->normal_price),
                'worker_cost' => format_angka($item->worker_cost),
            ])
            ->all();

        $data['additional_income'] = format_angka($data['additional_income'] ?? 0);
        $data['discount'] = format_angka($data['discount'] ?? 0);

        $data['daily_expenses'] = $this->record->expenses
            ->where('allocation', ExpenseAllocation::Daily)
            ->values()
            ->map(fn ($expense) => [
                'id' => $expense->id,
                'expense_category_id' => $expense->expense_category_id,
                'name' => $expense->name,
                'amount' => format_angka($expense->amount),
                'notes' => $expense->notes,
            ])
            ->all();

        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        /** @var BookkeepingPeriod $record */
        return app(BookkeepingPeriodService::class)->save($data, auth()->user(), $record);
    }

    protected function getSavedNotification(): ?Notification
    {
        $summary = $this->record->fresh('summary')->summary;

        return Notification::make()
            ->success()
            ->title('Pembukuan diperbarui')
            ->body($summary
                ? 'Omzet '.rupiah($summary->actual_revenue).' · Laba harian '.rupiah($summary->net_profit)
                : null);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make()
                ->using(fn (BookkeepingPeriod $record) => app(BookkeepingPeriodService::class)->delete($record, auth()->user())),
        ];
    }
}
