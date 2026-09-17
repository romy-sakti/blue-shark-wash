<?php

namespace App\Filament\Pages;

use App\Filament\Concerns\HasReportFilters;
use App\Services\ReportService;
use Filament\Actions\Action;
use Filament\Pages\Page;

class LaporanBulanan extends Page
{
    use HasReportFilters;

    protected static ?string $navigationIcon = 'tabler-calendar-month';

    protected static ?string $navigationGroup = 'Laporan';

    protected static ?string $navigationLabel = 'Laporan Bulanan';

    protected static ?string $title = 'Laporan Bulanan';

    protected static ?int $navigationSort = 3;

    protected static string $view = 'filament.pages.laporan-bulanan';

    public function mount(): void
    {
        $this->fillDefaultPeriod();
    }

    protected function reportGrain(): string
    {
        return 'month';
    }

    /**
     * @return array<string, mixed>
     */
    public function getReportProperty(): array
    {
        return app(ReportService::class)->summarize($this->reportFrom(), $this->reportUntil(), true);
    }

    public function getWeeksProperty()
    {
        return app(ReportService::class)->weeklyRows($this->reportFrom(), $this->reportUntil());
    }

    protected function getHeaderActions(): array
    {
        return [
            $this->printAction(),
            Action::make('csv')
                ->label('Unduh CSV')
                ->icon('tabler-download')
                ->action(function () {
                    $report = $this->report;

                    return $this->exportCsv('laporan-bulanan.csv', ['Indikator', 'Nilai'], array_merge([
                        ['Periode', $this->reportPeriodLabel()],
                        ['Total kendaraan', $report['total_units']],
                        ['Omzet', format_angka($report['actual_revenue'])],
                        ['Biaya pekerja', format_angka($report['worker_cost'])],
                        ['Operasional', format_angka($report['operational_cost'])],
                        ['Laba bersih bulan', format_angka($report['net_profit'])],
                        ['Margin laba %', $report['margin_percent']],
                        ['Hari operasi', $report['operating_days_label']],
                        ['Hari kalender', $report['calendar_days']],
                        ['Omzet rata-rata / hari operasi', format_angka($report['avg_revenue_per_day'])],
                        ['Kendaraan rata-rata / hari', $report['avg_units_per_day']],
                        ['Laba rata-rata / hari', format_angka($report['avg_profit_per_day'])],
                        ['Omzet / kendaraan', format_angka($report['revenue_per_unit'])],
                        ['Laba / kendaraan', format_angka($report['profit_per_unit'])],
                    ], collect($report['expenses_by_category'])->map(fn ($amount, $name) => [$name, format_angka($amount)])->values()->all()));
                }),
        ];
    }
}
