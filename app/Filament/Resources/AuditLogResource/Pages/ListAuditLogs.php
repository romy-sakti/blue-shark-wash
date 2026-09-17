<?php

namespace App\Filament\Resources\AuditLogResource\Pages;

use App\Filament\Resources\AuditLogResource;
use App\Models\AuditLog;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListAuditLogs extends ListRecords
{
    protected static string $resource = AuditLogResource::class;

    public function getTitle(): string
    {
        return 'Log aktivitas harian';
    }

    public function getDefaultActiveTab(): string|int|null
    {
        return 'today';
    }

    public function getTabs(): array
    {
        return [
            'today' => Tab::make('Hari ini')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereDate('created_at', today()))
                ->badge(AuditLog::query()->whereDate('created_at', today())->count()),
            'yesterday' => Tab::make('Kemarin')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereDate('created_at', today()->subDay()))
                ->badge(AuditLog::query()->whereDate('created_at', today()->subDay())->count()),
            'week' => Tab::make('7 hari')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('created_at', '>=', now()->startOfDay()->subDays(6)))
                ->badge(AuditLog::query()->where('created_at', '>=', now()->startOfDay()->subDays(6))->count()),
            'all' => Tab::make('Semua'),
        ];
    }
}
