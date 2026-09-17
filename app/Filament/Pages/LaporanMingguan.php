<?php

namespace App\Filament\Pages;

use App\Filament\Concerns\HasReportFilters;
use App\Services\ReportService;
use Filament\Actions\Action;
use Filament\Pages\Page;

class LaporanMingguan extends Page
{
    use HasReportFilters;

    protected static ?string $navigationIcon = 'tabler-calendar-week';

    protected static ?string $navigationGroup = 'Laporan';

    protected static ?string $navigationLabel = 'Laporan Mingguan';

    protected static ?string $title = 'Laporan Mingguan';

    protected static ?int $navigationSort = 2;

    protected static string $view = 'filament.pages.laporan-mingguan';

    public function mount(): void
    {
        $this->fillDefaultPeriod();
    }

    protected function reportGrain(): string
    {
        return 'week';
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
                        format_angka($row->actual_revenue),
                        format_angka($row->worker_cost_total),
                        format_angka($row->operational_cost),
                        format_angka($row->net_profit),
                    ]);

                    return $this->exportCsv('laporan-mingguan.csv', [
                        'Tanggal', 'Unit', 'Omzet', 'Pekerja', 'Operasional', 'Laba',
                    ], $rows);
                }),
        ];
    }
}
