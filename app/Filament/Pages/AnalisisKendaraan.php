<?php

namespace App\Filament\Pages;

use App\Filament\Concerns\HasReportFilters;
use App\Services\ReportService;
use Filament\Actions\Action;
use Filament\Pages\Page;

class AnalisisKendaraan extends Page
{
    use HasReportFilters;

    protected static ?string $navigationIcon = 'tabler-car';

    protected static ?string $navigationGroup = 'Laporan';

    protected static ?string $navigationLabel = 'Analisis Kendaraan';

    protected static ?string $title = 'Analisis Kendaraan';

    protected static ?int $navigationSort = 5;

    protected static string $view = 'filament.pages.analisis-kendaraan';

    public function mount(): void
    {
        $this->fillDefaultRange(now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString());
    }

    public function getRowsProperty()
    {
        return app(ReportService::class)->vehicleAnalysis($this->reportFrom(), $this->reportUntil());
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
                        $row['name'],
                        $row['units'],
                        format_angka($row['revenue']),
                        format_angka($row['worker_cost']),
                        format_angka($row['margin']),
                        $row['contribution'],
                    ]);

                    return $this->exportCsv('analisis-kendaraan.csv', [
                        'Kendaraan', 'Unit', 'Omzet', 'Pekerja', 'Margin', 'Kontribusi %',
                    ], $rows);
                }),
        ];
    }
}
