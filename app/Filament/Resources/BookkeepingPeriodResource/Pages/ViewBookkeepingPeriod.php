<?php

namespace App\Filament\Resources\BookkeepingPeriodResource\Pages;

use App\Filament\Resources\BookkeepingPeriodResource;
use App\Models\BookkeepingPeriod;
use App\Services\BookkeepingPeriodService;
use Filament\Actions;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewBookkeepingPeriod extends ViewRecord
{
    protected static string $resource = BookkeepingPeriodResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\Action::make('finalize')
                ->label('Finalkan')
                ->icon('tabler-circle-check')
                ->color('success')
                ->visible(fn () => $this->record->isDraft())
                ->requiresConfirmation()
                ->action(function () {
                    app(BookkeepingPeriodService::class)->finalize($this->record, auth()->user());
                    Notification::make()->title('Pembukuan difinalkan')->success()->send();
                }),
            Actions\Action::make('lock')
                ->label('Kunci periode')
                ->icon('tabler-lock')
                ->color('danger')
                ->visible(fn () => auth()->user()?->isOwner() && ! $this->record->isLocked())
                ->requiresConfirmation()
                ->action(function () {
                    app(BookkeepingPeriodService::class)->lock($this->record, auth()->user());
                    Notification::make()->title('Periode dikunci')->success()->send();
                }),
            Actions\Action::make('unlock')
                ->label('Buka kunci')
                ->icon('tabler-lock-open')
                ->visible(fn () => auth()->user()?->isOwner() && $this->record->isLocked())
                ->form([
                    Textarea::make('reason')->label('Alasan')->required(),
                ])
                ->action(function (array $data) {
                    app(BookkeepingPeriodService::class)->unlock($this->record, auth()->user(), $data['reason']);
                    Notification::make()->title('Kunci dibuka')->success()->send();
                }),
        ];
    }
}
