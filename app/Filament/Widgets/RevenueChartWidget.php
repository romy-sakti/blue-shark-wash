<?php

namespace App\Filament\Widgets;

use App\Models\DailySummary;
use App\Support\DateRange;
use Filament\Support\RawJs;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class RevenueChartWidget extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?string $heading = 'Omzet dan laba harian';

    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    protected function getData(): array
    {
        $range = DateRange::fromPreset(
            $this->filters['preset'] ?? 'today',
            $this->filters['from'] ?? null,
            $this->filters['until'] ?? null,
        );

        $from = $range->from;
        $until = $range->until;

        if ($from->isSameDay($until)) {
            $from = $until->copy()->subDays(13)->startOfDay();
        }

        $summaries = DailySummary::query()
            ->whereBetween('period_date', [$from->toDateString(), $until->toDateString()])
            ->orderBy('period_date')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Omzet',
                    'data' => $summaries->pluck('actual_revenue')->all(),
                    'borderColor' => '#0F766E',
                    'backgroundColor' => 'rgba(15, 118, 110, 0.12)',
                    'fill' => true,
                    'tension' => 0.3,
                ],
                [
                    'label' => 'Laba harian',
                    'data' => $summaries->pluck('net_profit')->all(),
                    'borderColor' => '#D97706',
                    'backgroundColor' => 'transparent',
                    'tension' => 0.3,
                ],
            ],
            'labels' => $summaries->map(fn (DailySummary $row) => $row->period_date->format('d M'))->all(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): RawJs
    {
        return RawJs::make(<<<'JS'
        {
            plugins: {
                tooltip: {
                    callbacks: {
                        label: (context) => {
                            const value = new Intl.NumberFormat('id-ID').format(context.parsed.y ?? 0)
                            return (context.dataset.label ?? '') + ': Rp ' + value
                        },
                    },
                },
            },
            scales: {
                y: {
                    ticks: {
                        callback: (value) => new Intl.NumberFormat('id-ID').format(value),
                    },
                },
            },
        }
        JS);
    }
}
