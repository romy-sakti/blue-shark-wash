<?php

namespace App\Filament\Widgets;

use App\Services\ReportService;
use App\Support\DateRange;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class FinancialStatsWidget extends BaseWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 1;

    protected ?string $heading = 'Ringkasan keuangan';

    protected function getStats(): array
    {
        $range = DateRange::fromPreset(
            $this->filters['preset'] ?? 'today',
            $this->filters['from'] ?? null,
            $this->filters['until'] ?? null,
        );

        $data = app(ReportService::class)->summarize(
            $range->from,
            $range->until,
            $range->includePeriodExpenses,
        );

        $label = app(ReportService::class)->rangeLabel($range);

        return [
            Stat::make('Omzet', rupiah($data['actual_revenue']))
                ->description($label.' · '.$data['total_units'].' kendaraan')
                ->icon('tabler-cash')
                ->color('primary'),
            Stat::make('Biaya Pekerja', rupiah($data['worker_cost']))
                ->description('Margin '.rupiah($data['margin']))
                ->icon('tabler-users')
                ->color('warning'),
            Stat::make('Operasional', rupiah($data['operational_cost']))
                ->description($range->includePeriodExpenses
                    ? 'Termasuk biaya bulanan'
                    : 'Pengeluaran harian saja')
                ->icon('tabler-droplet')
                ->color('gray'),
            Stat::make('Laba Bersih', rupiah($data['net_profit']))
                ->description('Margin laba '.$data['margin_percent'].'%')
                ->icon('tabler-chart-line')
                ->color($data['net_profit'] >= 0 ? 'success' : 'danger'),
        ];
    }
}
