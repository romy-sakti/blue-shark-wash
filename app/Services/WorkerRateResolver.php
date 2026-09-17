<?php

namespace App\Services;

use App\Models\Service;
use App\Models\WorkerRate;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Menentukan biaya pekerja yang di-snapshot ke transaksi.
 *
 * Tarif dimuat sekali per request, lalu dicocokkan di memori
 * supaya preview form tidak menembak query per layanan.
 */
class WorkerRateResolver
{
    private ?Collection $rates = null;

    public function resolve(Service $service, Carbon|string $date): int
    {
        $date = Carbon::parse($date)->toDateString();

        $specific = $this->rates()->first(
            fn (WorkerRate $rate) => $rate->service_id === $service->id
                && $rate->effective_from->toDateString() <= $date
        );

        if ($specific) {
            return (int) $specific->rate;
        }

        $byVehicle = $this->rates()->first(
            fn (WorkerRate $rate) => $rate->vehicle_type_id === $service->vehicle_type_id
                && $rate->service_id === null
                && $rate->effective_from->toDateString() <= $date
        );

        if ($byVehicle) {
            return (int) $byVehicle->rate;
        }

        return (int) $service->worker_cost;
    }

    private function rates(): Collection
    {
        return $this->rates ??= WorkerRate::query()
            ->orderByDesc('effective_from')
            ->orderByDesc('id')
            ->get();
    }
}
