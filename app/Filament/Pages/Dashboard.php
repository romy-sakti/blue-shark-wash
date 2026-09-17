<?php

namespace App\Filament\Pages;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;
use Illuminate\Contracts\Support\Htmlable;

class Dashboard extends BaseDashboard
{
    use HasFiltersForm;

    protected static ?string $navigationLabel = 'Dashboard';

    protected static ?string $title = 'Dasbor Pembukuan';

    protected static ?string $navigationIcon = 'tabler-home';

    public function getSubheading(): string|Htmlable|null
    {
        return 'Ringkasan omzet, biaya pekerja, operasional, dan laba Blue Shark Wash.';
    }

    public function filtersForm(Form $form): Form
    {
        return $form->schema([
            Section::make('Periode laporan')
                ->schema([
                    Select::make('preset')
                        ->label('Rentang')
                        ->options([
                            'today' => 'Hari ini',
                            'week' => 'Minggu ini',
                            'month' => 'Bulan ini',
                            'year' => 'Tahun ini',
                            'custom' => 'Kustom',
                        ])
                        ->default('today')
                        ->live(),
                    DatePicker::make('from')
                        ->label('Dari')
                        ->native(false)
                        ->visible(fn ($get) => $get('preset') === 'custom')
                        ->required(fn ($get) => $get('preset') === 'custom'),
                    DatePicker::make('until')
                        ->label('Sampai')
                        ->native(false)
                        ->visible(fn ($get) => $get('preset') === 'custom')
                        ->required(fn ($get) => $get('preset') === 'custom'),
                ])
                ->columns(3),
        ]);
    }

    public function getColumns(): int|string|array
    {
        return [
            'default' => 1,
            'md' => 2,
            'xl' => 4,
        ];
    }
}
