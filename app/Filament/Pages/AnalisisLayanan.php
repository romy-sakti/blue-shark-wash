<?php

namespace App\Filament\Pages;

use App\Filament\Concerns\HasReportFilters;
use App\Services\ReportService;
use Filament\Actions\Action;
use Filament\Pages\Page;

class AnalisisLayanan extends Page
{
    use HasReportFilters;

    protected static ?string $navigationIcon = 'tabler-wash';

    protected static ?string $navigationGroup = 'Laporan';

    protected static ?string $navigationLabel = 'Analisis Layanan';

    protected static ?string $title = 'Analisis Layanan';

    protected static ?int $navigationSort = 6;

    protected static string $view = 'filament.pages.analisis-layanan';

    public function mount(): void
    {
        $this->fillDefaultRange(now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString());
    }

    public function getRowsProperty()
    {
        return app(ReportService::class)->serviceAnalysis($this->reportFrom(), $this->reportUntil());
    }

    protected function getHeaderActions(): array
    {
        return [
            $this->printAction(),
            Action::make('csv')
                ->label('Unduh CSV')
                ->icon('tabler-download')
                ->action(function () {
                    $rows = $this->rows->map(fn ($row) => [
                        $row['vehicle'],
                        $row['name'],
                        $row['units'],
                        format_angka($row['revenue']),
                        format_angka($row['worker_cost']),
                        format_angka($row['margin']),
                        $row['avg_per_day'],
                    ]);

                    return $this->exportCsv('analisis-layanan.csv', [
                        'Kendaraan', 'Layanan', 'Unit', 'Omzet', 'Pekerja', 'Margin', 'Rata-rata / hari',
                    ], $rows);
                }),
        ];
    }
}
