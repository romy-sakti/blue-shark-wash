<?php

namespace App\Filament\Resources\BookkeepingPeriodResource\Pages;

use App\Filament\Resources\BookkeepingPeriodResource;
use App\Models\BookkeepingPeriod;
use App\Services\BookkeepingPeriodService;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateBookkeepingPeriod extends CreateRecord
{
    protected static string $resource = BookkeepingPeriodResource::class;

    protected static ?string $title = 'Input Pembukuan Harian';

    protected function handleRecordCreation(array $data): Model
    {
        return app(BookkeepingPeriodService::class)->save($data, auth()->user());
    }

    protected function getCreatedNotification(): ?Notification
    {
        /** @var BookkeepingPeriod $record */
        $record = $this->record;
        $summary = $record->summary;

        return Notification::make()
            ->success()
            ->title('Pembukuan tersimpan')
            ->body($summary
                ? 'Omzet '.rupiah($summary->actual_revenue).' · Laba harian '.rupiah($summary->net_profit)
                : null);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }
}
