<?php

namespace App\Filament\Forms;

use App\Filament\Support\MoneyInput;
use App\Models\BookkeepingPeriod;
use App\Models\ExpenseCategory;
use App\Models\Service;
use App\Services\BookkeepingCalculator;
use App\Services\WorkerRateResolver;
use App\Support\CalculatedBookkeeping;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Get;
use Illuminate\Support\HtmlString;

class BookkeepingForm
{
    /**
     * @return array<int, \Filament\Forms\Components\Component>
     */
    public static function schema(?BookkeepingPeriod $record = null): array
    {
        $keepIds = $record?->items()
            ->where('is_custom', false)
            ->whereNotNull('service_id')
            ->pluck('service_id')
            ->all() ?? [];

        $services = Service::query()
            ->with('vehicleType')
            ->where(function ($query) use ($keepIds) {
                $query->where('is_active', true)
                    ->whereHas('vehicleType', fn ($q) => $q->onDailyForm());

                if ($keepIds !== []) {
                    $query->orWhereIn('id', $keepIds);
                }
            })
            ->orderBy('vehicle_type_id')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $serviceSections = $services
            ->groupBy(fn (Service $service) => $service->vehicleType?->name ?? 'Lainnya')
            ->map(function ($group, $typeName) {
                return Section::make(strtoupper($typeName))
                    ->compact()
                    ->schema(
                        $group->map(fn (Service $service) => TextInput::make('quantities.'.$service->id)
                            ->label($service->name)
                            ->numeric()
                            ->minValue(0)
                            ->default(0)
                            ->suffix('unit')
                            ->live(onBlur: true)
                            ->helperText(rupiah($service->normal_price).' · pekerja '.rupiah($service->worker_cost))
                        )->all()
                    )
                    ->columns(3);
            })
            ->values()
            ->all();

        return [
            Section::make('Periode')
                ->schema([
                    DatePicker::make('period_date')
                        ->label('Tanggal')
                        ->required()
                        ->native(false)
                        ->displayFormat('d F Y')
                        ->default(now())
                        ->unique(ignoreRecord: true)
                        ->live()
                        ->disabled(fn (?BookkeepingPeriod $record) => $record?->isLocked() && ! auth()->user()?->isOwner()),
                    Textarea::make('notes')
                        ->label('Keterangan')
                        ->rows(2)
                        ->columnSpanFull(),
                    Textarea::make('correction_reason')
                        ->label('Alasan koreksi')
                        ->helperText('Wajib diisi jika mengubah pembukuan yang sudah dikunci.')
                        ->visible(fn (?BookkeepingPeriod $record) => $record?->isLocked())
                        ->required(fn (?BookkeepingPeriod $record) => (bool) $record?->isLocked())
                        ->columnSpanFull(),
                ])
                ->columns(2),

            ...$serviceSections,

            Section::make('Layanan lainnya')
                ->description('Di luar paket Motor/Mobil — sepeda listrik, semir, kilap body, semprot air, dan sejenisnya. Nama dan tarif ditulis bebas. Tetap dihitung sebagai pemasukan.')
                ->schema([
                    Repeater::make('extra_lines')
                        ->label('')
                        ->schema([
                            Hidden::make('id'),
                            TextInput::make('name')
                                ->label('Nama layanan')
                                ->placeholder('Contoh: Semir Body Motor')
                                ->required()
                                ->live(onBlur: true),
                            TextInput::make('quantity')
                                ->label('Jumlah')
                                ->numeric()
                                ->minValue(1)
                                ->default(1)
                                ->live(onBlur: true),
                            MoneyInput::make('amount')
                                ->label('Nominal')
                                ->required()
                                ->live(onBlur: true)
                                ->helperText('Uang yang diterima'),
                            MoneyInput::make('worker_cost')
                                ->label('Biaya pekerja')
                                ->default(0)
                                ->live(onBlur: true)
                                ->helperText('Isi 0 jika tidak ada.'),
                        ])
                        ->columns(4)
                        ->addActionLabel('Tambah layanan lainnya')
                        ->defaultItems(0)
                        ->itemLabel(fn (array $state): ?string => filled($state['name'] ?? null)
                            ? ($state['name'].' · '.rupiah(parse_rupiah($state['amount'] ?? 0)))
                            : null),
                ]),

            Section::make('Penyesuaian pendapatan')
                ->schema([
                    MoneyInput::make('additional_income')
                        ->label('Pendapatan tambahan')
                        ->default(0)
                        ->live(onBlur: true)
                        ->helperText('Pembayaran lebih, tip, uang tidak diambil.'),
                    MoneyInput::make('discount')
                        ->label('Potongan')
                        ->default(0)
                        ->live(onBlur: true)
                        ->helperText('Pembayaran kurang. Harga paket tidak berubah.'),
                ])
                ->columns(2),

            Section::make('Pengeluaran harian')
                ->schema([
                    Repeater::make('daily_expenses')
                        ->label('')
                        ->schema([
                            Hidden::make('id'),
                            Select::make('expense_category_id')
                                ->label('Kategori')
                                ->options(fn () => ExpenseCategory::query()->active()->orderBy('sort_order')->pluck('name', 'id'))
                                ->required()
                                ->searchable(),
                            TextInput::make('name')->label('Nama / keterangan')->required(),
                            MoneyInput::make('amount')->label('Nominal')->required()->live(onBlur: true),
                            TextInput::make('notes')->label('Catatan'),
                        ])
                        ->columns(4)
                        ->addActionLabel('Tambah pengeluaran')
                        ->defaultItems(0),
                ]),

            Section::make('Perhitungan otomatis')
                ->description('Harga dan biaya pekerja di-snapshot saat disimpan. Perubahan master tidak mengubah data lama.')
                ->schema([
                    Placeholder::make('preview')
                        ->hiddenLabel()
                        ->columnSpanFull()
                        ->content(fn (Get $get) => new HtmlString(
                            view('filament.components.bookkeeping-preview', [
                                'calc' => self::preview($get),
                            ])->render()
                        )),
                ]),
        ];
    }

    public static function preview(Get $get): CalculatedBookkeeping
    {
        static $services = null;
        $services ??= Service::query()->with('vehicleType')->get()->keyBy('id');
        $resolver = app(WorkerRateResolver::class);
        $date = $get('period_date') ?: now();
        $lines = [];

        foreach ($get('quantities') ?? [] as $serviceId => $quantity) {
            $service = $services->get((int) $serviceId);

            if (! $service) {
                continue;
            }

            $lines[] = [
                'service_id' => $service->id,
                'vehicle_type_id' => $service->vehicle_type_id,
                'service_name' => $service->name,
                'vehicle_type_name' => $service->vehicleType?->name,
                'quantity' => (int) $quantity,
                'normal_price' => (int) $service->normal_price,
                'worker_cost' => $resolver->resolve($service, $date),
            ];
        }

        foreach ($get('extra_lines') ?? [] as $extra) {
            $name = trim((string) ($extra['name'] ?? ''));
            $quantity = max(0, (int) ($extra['quantity'] ?? 1));
            $amount = parse_rupiah($extra['amount'] ?? 0);

            if ($name === '' || $quantity <= 0 || $amount <= 0) {
                continue;
            }

            $lines[] = [
                'service_id' => null,
                'vehicle_type_id' => null,
                'service_name' => $name,
                'vehicle_type_name' => 'Lainnya',
                'quantity' => $quantity,
                'normal_price' => $amount,
                'worker_cost' => parse_rupiah($extra['worker_cost'] ?? 0),
            ];
        }

        $operational = collect($get('daily_expenses') ?? [])
            ->sum(fn ($row) => parse_rupiah($row['amount'] ?? 0));

        return app(BookkeepingCalculator::class)->calculate(
            $lines,
            parse_rupiah($get('additional_income') ?? 0),
            parse_rupiah($get('discount') ?? 0),
            $operational,
        );
    }
}
