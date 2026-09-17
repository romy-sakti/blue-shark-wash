<?php

namespace Database\Seeders;

use App\Enums\ExpenseAllocation;
use App\Models\BookkeepingPeriod;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Service;
use App\Models\User;
use App\Services\BookkeepingPeriodService;
use Illuminate\Database\Seeder;

class DemoBookkeepingSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::query()->where('email', 'admin@blueshark.test')->first();
        $owner = User::query()->where('email', 'owner@blueshark.test')->first();
        $service = app(BookkeepingPeriodService::class);

        $map = Service::query()
            ->with('vehicleType')
            ->get()
            ->mapWithKeys(fn (Service $item) => [$item->vehicleType->name.'|'.$item->name => $item->id]);

        $qty = fn (string $vehicle, string $serviceName, int $quantity): array => [
            $map[$vehicle.'|'.$serviceName] => $quantity,
        ];

        $days = [
            '2026-09-14' => [
                'quantities' => $qty('Motor', 'Paket 1', 2)
                    + $qty('Motor', 'Paket 2', 3)
                    + $qty('Motor', 'Paket 3', 2),
                'additional_income' => 0,
                'discount' => 0,
                'notes' => 'Contoh pembukuan 14 September',
                'daily_expenses' => [
                    [
                        'expense_category_id' => ExpenseCategory::query()->where('name', 'Sabun')->value('id'),
                        'name' => 'Sabun cuci',
                        'amount' => 15000,
                    ],
                ],
            ],
            '2026-09-15' => [
                'quantities' => $qty('Motor', 'Paket 1', 3)
                    + $qty('Motor', 'Paket 2', 2)
                    + $qty('Motor', 'Paket 3', 4),
                'extra_lines' => [
                    [
                        'name' => 'Cuci Sepeda Listrik',
                        'quantity' => 1,
                        'amount' => 10000,
                        'worker_cost' => 5000,
                    ],
                ],
                'additional_income' => 2000,
                'discount' => 0,
                'notes' => 'Paket motor + layanan lainnya (sepeda listrik, semir). Satu pelanggan Paket 3 bayar lebih Rp2.000.',
                'daily_expenses' => [
                    [
                        'expense_category_id' => ExpenseCategory::query()->where('name', 'Bahan Cuci')->value('id'),
                        'name' => 'Shampo',
                        'amount' => 20000,
                    ],
                ],
            ],
            '2026-09-16' => [
                'quantities' => $qty('Motor', 'Paket 1', 1)
                    + $qty('Motor', 'Paket 2', 1)
                    + $qty('Motor', 'Paket 3', 3)
                    + $qty('Mobil', 'Paket 1', 1),
                'additional_income' => 5000,
                'discount' => 3000,
                'notes' => 'Hari ini: ada tambahan, potongan, dan layanan lainnya.',
                'extra_lines' => [
                    [
                        'name' => 'Semir Body Motor',
                        'quantity' => 1,
                        'amount' => 10000,
                        'worker_cost' => 0,
                    ],
                ],
                'daily_expenses' => [],
            ],
        ];

        foreach ($days as $date => $payload) {
            $existing = BookkeepingPeriod::query()->whereDate('period_date', $date)->first();

            $actor = $existing?->isLocked() ? $owner : $admin;

            $period = $service->save([
                'period_date' => $date,
                'quantities' => $payload['quantities'],
                'additional_income' => $payload['additional_income'],
                'discount' => $payload['discount'],
                'notes' => $payload['notes'],
                'daily_expenses' => $payload['daily_expenses'],
                'extra_lines' => $payload['extra_lines'] ?? [],
                'correction_reason' => $existing?->isLocked() ? 'Seed ulang data contoh' : null,
            ], $actor, $existing);

            if ($period->isDraft()) {
                $service->finalize($period, $admin);
            }
        }

        Expense::query()->updateOrCreate(
            [
                'name' => 'Tagihan listrik September',
                'attribution_month' => '2026-09-01',
                'allocation' => ExpenseAllocation::Period,
            ],
            [
                'expense_category_id' => ExpenseCategory::query()->where('name', 'Listrik')->value('id'),
                'expense_date' => '2026-09-16',
                'amount' => 300000,
                'notes' => 'Biaya bulanan September 2026',
                'created_by' => $owner->id,
            ],
        );

        Expense::query()->updateOrCreate(
            [
                'name' => 'Tagihan PDAM September',
                'attribution_month' => '2026-09-01',
                'allocation' => ExpenseAllocation::Period,
            ],
            [
                'expense_category_id' => ExpenseCategory::query()->where('name', 'Air PDAM')->value('id'),
                'expense_date' => '2026-09-16',
                'amount' => 200000,
                'notes' => 'Biaya bulanan September 2026',
                'created_by' => $owner->id,
            ],
        );

        $locked = BookkeepingPeriod::query()->whereDate('period_date', '2026-09-14')->first();

        if ($locked && ! $locked->isLocked()) {
            $service->lock($locked, $owner);
        }
    }
}
