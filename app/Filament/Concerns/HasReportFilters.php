<?php

namespace App\Filament\Concerns;

use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Get;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Form;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Filter periode laporan menurut grain halaman.
 *
 * day = satu tanggal, week = Senin–Minggu, month = satu bulan kalender, range = Dari–Sampai.
 *
 * @see docs/IMPLEMENTASI.md bagian "Filter periode laporan"
 */
trait HasReportFilters
{
    use InteractsWithForms;

    public ?array $data = [];

    /**
     * day | week | month | range
     */
    protected function reportGrain(): string
    {
        return 'range';
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema($this->reportFilterSchema())
            ->columns($this->reportGrain() === 'range' ? 2 : 1)
            ->statePath('data');
    }

    /**
     * @return array<int, \Filament\Forms\Components\Field>
     */
    protected function reportFilterSchema(): array
    {
        return match ($this->reportGrain()) {
            'day' => [
                DatePicker::make('date')
                    ->label('Tanggal')
                    ->native(false)
                    ->displayFormat('d F Y')
                    ->firstDayOfWeek(1)
                    ->live()
                    ->required()
                    ->helperText('Laporan untuk satu hari operasional. Laba hari ini belum dikurangi listrik/PDAM.'),
            ],
            'week' => [
                DatePicker::make('date')
                    ->label('Minggu')
                    ->native(false)
                    ->displayFormat('d F Y')
                    ->firstDayOfWeek(1)
                    ->live()
                    ->required()
                    ->afterStateUpdated(function (?string $state, callable $set): void {
                        if ($state) {
                            $set('date', Carbon::parse($state)->startOfWeek(Carbon::MONDAY)->toDateString());
                        }
                    })
                    ->helperText(function (Get $get): string {
                        $start = Carbon::parse($get('date') ?? now())->startOfWeek(Carbon::MONDAY);
                        $end = $start->copy()->endOfWeek(Carbon::SUNDAY);

                        return 'Senin–Minggu · '.tanggal_id($start).' s/d '.tanggal_id($end).'. Laba minggu belum dikurangi listrik/PDAM.';
                    }),
            ],
            'month' => [
                Select::make('month')
                    ->label('Bulan')
                    ->options($this->monthOptions())
                    ->native(false)
                    ->live()
                    ->required()
                    ->helperText('Satu bulan kalender. Laba bersih bulan = omzet − pekerja − bahan − listrik/PDAM.'),
            ],
            default => [
                DatePicker::make('from')
                    ->label('Dari')
                    ->native(false)
                    ->displayFormat('d F Y')
                    ->live()
                    ->required(),
                DatePicker::make('until')
                    ->label('Sampai')
                    ->native(false)
                    ->displayFormat('d F Y')
                    ->live()
                    ->required(),
            ],
        };
    }

    /**
     * @return array<string, string>
     */
    protected function monthOptions(): array
    {
        $options = [];
        $cursor = now()->startOfMonth();

        for ($i = 0; $i < 36; $i++) {
            $options[$cursor->toDateString()] = tanggal_id($cursor, 'F Y');
            $cursor->subMonthNoOverflow();
        }

        return $options;
    }

    protected function fillDefaultRange(?string $from = null, ?string $until = null): void
    {
        $this->form->fill([
            'from' => $from ?? now()->startOfMonth()->toDateString(),
            'until' => $until ?? now()->toDateString(),
        ]);
    }

    protected function fillDefaultPeriod(): void
    {
        match ($this->reportGrain()) {
            'day' => $this->form->fill(['date' => now()->toDateString()]),
            'week' => $this->form->fill(['date' => now()->startOfWeek(Carbon::MONDAY)->toDateString()]),
            'month' => $this->form->fill(['month' => now()->startOfMonth()->toDateString()]),
            default => $this->fillDefaultRange(),
        };
    }

    public function reportFrom(): Carbon
    {
        return match ($this->reportGrain()) {
            'day' => Carbon::parse($this->data['date'] ?? now())->startOfDay(),
            'week' => Carbon::parse($this->data['date'] ?? now())->startOfWeek(Carbon::MONDAY)->startOfDay(),
            'month' => Carbon::parse($this->data['month'] ?? now())->startOfMonth()->startOfDay(),
            default => Carbon::parse($this->data['from'] ?? now()->startOfMonth())->startOfDay(),
        };
    }

    public function reportUntil(): Carbon
    {
        return match ($this->reportGrain()) {
            'day' => Carbon::parse($this->data['date'] ?? now())->endOfDay(),
            'week' => Carbon::parse($this->data['date'] ?? now())->startOfWeek(Carbon::MONDAY)->endOfWeek(Carbon::SUNDAY)->endOfDay(),
            'month' => Carbon::parse($this->data['month'] ?? now())->endOfMonth()->endOfDay(),
            default => Carbon::parse($this->data['until'] ?? now())->endOfDay(),
        };
    }

    public function reportPeriodLabel(): string
    {
        return match ($this->reportGrain()) {
            'day' => tanggal_id($this->reportFrom()),
            'month' => tanggal_id($this->reportFrom(), 'F Y'),
            default => tanggal_id($this->reportFrom()).' s/d '.tanggal_id($this->reportUntil()),
        };
    }

    /**
     * @param  list<string>  $headers
     * @param  iterable<int, list<string|int|float|null>>  $rows
     */
    protected function exportCsv(string $filename, array $headers, iterable $rows): StreamedResponse
    {
        return response()->streamDownload(function () use ($headers, $rows) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($handle, $headers);

            foreach ($rows as $row) {
                fputcsv($handle, $row);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    protected function printAction(): Action
    {
        return Action::make('print')
            ->label('Cetak / PDF')
            ->icon('tabler-printer')
            ->color('gray')
            ->extraAttributes(['class' => 'report-print-hide'])
            ->tooltip('Buka dialog cetak. Pilih Save as PDF untuk menyimpan file.')
            ->action('printReport');
    }

    public function printReport(): void
    {
        $title = static::$title ?? 'Laporan';
        $filename = json_encode(
            $title.' — '.$this->reportPeriodLabel(),
            JSON_UNESCAPED_UNICODE
        );

        $this->js(<<<JS
            (() => {
                const sheet = document.querySelector('.report-sheet');
                const orientation = sheet?.dataset.orientation === 'landscape' ? 'landscape' : 'portrait';
                const previousTitle = document.title;

                document.title = {$filename};
                document.body.classList.add('is-printing-report');

                let pageStyle = document.getElementById('report-page-size');
                if (!pageStyle) {
                    pageStyle = document.createElement('style');
                    pageStyle.id = 'report-page-size';
                    document.head.appendChild(pageStyle);
                }
                pageStyle.textContent = '@page { size: A4 ' + orientation + '; margin: 12mm 10mm 14mm; }';

                const restore = () => {
                    document.title = previousTitle;
                    document.body.classList.remove('is-printing-report');
                    window.removeEventListener('afterprint', restore);
                };

                window.addEventListener('afterprint', restore);
                window.setTimeout(() => window.print(), 50);
            })();
        JS);
    }
}
