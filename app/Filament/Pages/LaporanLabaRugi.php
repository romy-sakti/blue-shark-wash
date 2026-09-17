<?php

namespace App\Filament\Pages;

use App\Filament\Concerns\HasReportFilters;
use App\Services\ReportService;
use Filament\Actions\Action;
use Filament\Pages\Page;

class LaporanLabaRugi extends Page
{
    use HasReportFilters;

    protected static ?string $navigationIcon = 'tabler-report-money';

    protected static ?string $navigationGroup = 'Laporan';

    protected static ?string $navigationLabel = 'Laba Rugi';

    protected static ?string $title = 'Laporan Laba Rugi';

    protected static ?int $navigationSort = 4;

    protected static string $view = 'filament.pages.laporan-laba-rugi';

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
        return app(ReportService::class)->profitLoss($this->reportFrom(), $this->reportUntil(), true);
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
                    $rows = [
                        ['Periode', $this->reportPeriodLabel()],
                        ['Pendapatan layanan', format_angka($report['normal_revenue'])],
                        ['Pendapatan tambahan', format_angka($report['additional_income'])],
                        ['Potongan', '-'.format_angka($report['discount'])],
                        ['Pendapatan aktual', format_angka($report['actual_revenue'])],
                        ['Biaya pekerja', format_angka($report['worker_cost'])],
                    ];

                    foreach ($report['expenses_by_category'] as $name => $amount) {
                        $rows[] = [$name, format_angka($amount)];
                    }

                    $rows[] = ['Total biaya', format_angka($report['total_cost'])];
                    $rows[] = ['Laba bersih bulan', format_angka($report['net_profit'])];

                    return $this->exportCsv('laba-rugi.csv', ['Akun', 'Nominal'], $rows);
                }),
        ];
    }
}
