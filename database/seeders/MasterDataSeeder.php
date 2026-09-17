<?php

namespace Database\Seeders;

use App\Enums\AdjustmentDirection;
use App\Models\BookkeepingItem;
use App\Models\ExpenseCategory;
use App\Models\IncomeAdjustmentType;
use App\Models\Service;
use App\Models\User;
use App\Models\VehicleType;
use App\Models\WorkerRate;
use Illuminate\Database\Seeder;

class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        $ownerId = User::query()->where('email', 'owner@blueshark.test')->value('id');

        $motor = VehicleType::query()->updateOrCreate(['name' => 'Motor'], [
            'description' => 'Sepeda motor',
            'sort_order' => 1,
            'is_active' => true,
            'appears_on_form' => true,
        ]);

        $mobil = VehicleType::query()->updateOrCreate(['name' => 'Mobil'], [
            'description' => 'Mobil penumpang',
            'sort_order' => 2,
            'is_active' => true,
            'appears_on_form' => true,
        ]);

        $obsoleteIds = VehicleType::query()->whereIn('name', ['Sepeda Listrik', 'Lainnya'])->pluck('id');

        if ($obsoleteIds->isNotEmpty()) {
            $serviceIds = Service::query()->whereIn('vehicle_type_id', $obsoleteIds)->pluck('id');

            BookkeepingItem::query()
                ->where(function ($query) use ($obsoleteIds, $serviceIds) {
                    $query->whereIn('vehicle_type_id', $obsoleteIds);

                    if ($serviceIds->isNotEmpty()) {
                        $query->orWhereIn('service_id', $serviceIds);
                    }
                })
                ->update([
                    'vehicle_type_id' => null,
                    'service_id' => null,
                    'is_custom' => true,
                ]);

            VehicleType::query()->whereIn('id', $obsoleteIds)->delete();
        }

        $services = [
            [$motor->id, 'Paket 1', 13000, 5000, 1],
            [$motor->id, 'Paket 2', 15000, 5000, 2],
            [$motor->id, 'Paket 3', 18000, 5000, 3],
            [$mobil->id, 'Paket 1', 40000, 25000, 1],
            [$mobil->id, 'Paket 2', 50000, 25000, 2],
            [$mobil->id, 'Paket 3', 65000, 25000, 3],
        ];

        foreach ($services as [$vehicleTypeId, $name, $price, $worker, $order]) {
            Service::query()->updateOrCreate(
                ['vehicle_type_id' => $vehicleTypeId, 'name' => $name],
                [
                    'normal_price' => $price,
                    'worker_cost' => $worker,
                    'sort_order' => $order,
                    'is_active' => true,
                ],
            );
        }

        foreach ([
            [$motor->id, 5000],
            [$mobil->id, 25000],
        ] as [$vehicleTypeId, $rate]) {
            WorkerRate::query()->updateOrCreate(
                [
                    'vehicle_type_id' => $vehicleTypeId,
                    'service_id' => null,
                    'effective_from' => '2026-09-01',
                ],
                [
                    'rate' => $rate,
                    'notes' => 'Tarif awal sesuai PRD',
                    'created_by' => $ownerId,
                ],
            );
        }

        $categories = [
            'Listrik',
            'Air PDAM',
            'Shampo',
            'Pengkilap body',
            'Semir ban',
            'Lap',
            'Sabun',
            'Perawatan jet cleaner',
            'Peralatan',
            'Sewa',
            'Transportasi',
            'Bahan Cuci',
            'Lainnya',
        ];

        foreach ($categories as $index => $name) {
            ExpenseCategory::query()->updateOrCreate(
                ['name' => $name],
                ['sort_order' => $index + 1, 'is_active' => true],
            );
        }

        $adjustmentTypes = [
            ['Tambahan pembayaran', AdjustmentDirection::Increase],
            ['Tip', AdjustmentDirection::Increase],
            ['Pembayaran lebih', AdjustmentDirection::Increase],
            ['Layanan khusus', AdjustmentDirection::Increase],
            ['Potongan', AdjustmentDirection::Decrease],
            ['Pembayaran kurang', AdjustmentDirection::Decrease],
            ['Lainnya', AdjustmentDirection::Increase],
        ];

        foreach ($adjustmentTypes as [$name, $direction]) {
            IncomeAdjustmentType::query()->updateOrCreate(
                ['name' => $name],
                ['direction' => $direction, 'is_active' => true],
            );
        }
    }
}
