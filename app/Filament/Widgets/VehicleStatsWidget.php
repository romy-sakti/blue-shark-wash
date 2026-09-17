<?php

namespace App\Filament\Widgets;

use App\Services\ReportService;
use App\Support\DateRange;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class VehicleStatsWidget extends BaseWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 2;

    protected ?string $heading = 'Per kendaraan';

    protected ?string $description = 'Unit dan omzet pada periode yang dipilih';

    protected function getColumns(): int
    {
        return 4;
    }

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

        $byName = collect($data['vehicle_stats'])->keyBy('name');
        $blank = ['name' => '', 'units' => 0, 'revenue' => 0];
        $motor = $byName->get('Motor', $blank);
        $mobil = $byName->get('Mobil', $blank);
        $lainnya = $byName->except(['Motor', 'Mobil'])->reduce(
            fn (array $carry, array $row) => [
                'units' => $carry['units'] + (int) $row['units'],
                'revenue' => $carry['revenue'] + (int) $row['revenue'],
            ],
            ['units' => 0, 'revenue' => 0],
        );

        return [
            Stat::make('Motor', number_format((int) $motor['units'], 0, ',', '.').' unit')
                ->description(rupiah($motor['revenue']))
                ->icon('tabler-motorbike')
                ->color('primary'),
            Stat::make('Mobil', number_format((int) $mobil['units'], 0, ',', '.').' unit')
                ->description(rupiah($mobil['revenue']))
                ->icon('tabler-car')
                ->color('info'),
            Stat::make('Lainnya', number_format((int) $lainnya['units'], 0, ',', '.').' unit')
                ->description(rupiah($lainnya['revenue']))
                ->icon('tabler-wash')
                ->color('gray'),
            Stat::make('Total', number_format((int) $data['total_units'], 0, ',', '.').' unit')
                ->description('Omzet/unit '.rupiah($data['revenue_per_unit']).' · Laba/unit '.rupiah($data['profit_per_unit']))
                ->icon('tabler-sum')
                ->color('success'),
        ];
    }
}
