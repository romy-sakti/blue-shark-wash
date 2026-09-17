<?php

namespace App\Filament\Pages;

use App\Filament\Concerns\HasReportFilters;
use App\Services\ReportService;
use Filament\Actions\Action;
use Filament\Pages\Page;

class LaporanHarian extends Page
{
    use HasReportFilters;

    protected static ?string $navigationIcon = 'tabler-calendar-event';

    protected static ?string $navigationGroup = 'Laporan';

    protected static ?string $navigationLabel = 'Laporan Harian';

    protected static ?string $title = 'Laporan Harian';

    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.pages.laporan-harian';

    public function mount(): void
    {
        $this->fillDefaultPeriod();
    }

    protected function reportGrain(): string
    {
        return 'day';
    }

    /**
     * @return array<string, mixed>
     */
    public function getReportProperty(): array
    {
        return app(ReportService::class)->summarize($this->reportFrom(), $this->reportUntil(), false);
    }

    protected function getHeaderActions(): array
    {
        return [
            $this->printAction(),
            Action::make('csv')
                ->label('Unduh CSV')
                ->icon('tabler-download')
                ->action(function () {
                    $rows = collect($this->report['summaries'])->map(fn ($row) => [
                        $row->period_date->toDateString(),
                        $row->total_units,
                        format_angka($row->normal_revenue),
                        format_angka($row->additional_income),
                        format_angka($row->discount),
                        format_angka($row->actual_revenue),
                        format_angka($row->worker_cost_total),
                        format_angka($row->operational_cost),
                        format_angka($row->net_profit),
                    ]);

                    return $this->exportCsv('laporan-harian.csv', [
                        'Tanggal', 'Unit', 'Pendapatan normal', 'Tambahan', 'Potongan', 'Omzet', 'Pekerja', 'Operasional', 'Laba',
                    ], $rows);
                }),
        ];
    }
}
