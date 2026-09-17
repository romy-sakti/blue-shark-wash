<?php

namespace App\Services;

use App\Support\CalculatedBookkeeping;
use Illuminate\Support\Collection;

class BookkeepingCalculator
{
    /**
     * @param  array<int, array{quantity:int, normal_price:int, worker_cost:int, service_id?:int, vehicle_type_id?:int, service_name?:string, vehicle_type_name?:string}>  $lines
     */
    public function calculate(
        array $lines,
        int $additionalIncome = 0,
        int $discount = 0,
        int $operationalCost = 0,
    ): CalculatedBookkeeping {
        $normalized = [];
        $totalUnits = 0;
        $normalRevenue = 0;
        $workerCostTotal = 0;

        foreach ($lines as $line) {
            $quantity = max(0, (int) ($line['quantity'] ?? 0));

            if ($quantity === 0) {
                continue;
            }

            $normalPrice = (int) ($line['normal_price'] ?? 0);
            $workerCost = (int) ($line['worker_cost'] ?? 0);
            $lineRevenue = $quantity * $normalPrice;
            $lineWorker = $quantity * $workerCost;

            $totalUnits += $quantity;
            $normalRevenue += $lineRevenue;
            $workerCostTotal += $lineWorker;

            $normalized[] = [
                'service_id' => $line['service_id'] ?? null,
                'vehicle_type_id' => $line['vehicle_type_id'] ?? null,
                'service_name' => $line['service_name'] ?? null,
                'vehicle_type_name' => $line['vehicle_type_name'] ?? null,
                'quantity' => $quantity,
                'normal_price' => $normalPrice,
                'worker_cost' => $workerCost,
                'actual_revenue' => $lineRevenue,
                'worker_total' => $lineWorker,
                'margin' => $lineRevenue - $lineWorker,
            ];
        }

        $actualRevenue = $normalRevenue + $additionalIncome - $discount;
        $margin = $actualRevenue - $workerCostTotal;
        $netProfit = $margin - $operationalCost;

        return new CalculatedBookkeeping(
            totalUnits: $totalUnits,
            normalRevenue: $normalRevenue,
            additionalIncome: $additionalIncome,
            discount: $discount,
            actualRevenue: $actualRevenue,
            workerCostTotal: $workerCostTotal,
            operationalCost: $operationalCost,
            margin: $margin,
            netProfit: $netProfit,
            lines: $normalized,
            vehicleBreakdown: $this->groupBreakdown($normalized, 'vehicle_type_id', 'vehicle_type_name'),
            serviceBreakdown: $this->groupBreakdown($normalized, 'service_id', 'service_name'),
        );
    }

    /**
     * @param  array<int, array<string, mixed>>  $lines
     * @return array<int, array<string, mixed>>
     */
    private function groupBreakdown(array $lines, string $idKey, string $nameKey): array
    {
        return Collection::make($lines)
            ->groupBy(function (array $line) use ($idKey, $nameKey) {
                if (! empty($line[$idKey])) {
                    return 'id:'.$line[$idKey];
                }

                return 'name:'.($line[$nameKey] ?? 'Lainnya');
            })
            ->map(function (Collection $group) use ($idKey, $nameKey) {
                $first = $group->first();
                $units = (int) $group->sum('quantity');
                $revenue = (int) $group->sum('actual_revenue');
                $worker = (int) $group->sum('worker_total');

                return [
                    'id' => $first[$idKey] ?? null,
                    'name' => $first[$nameKey] ?? 'Lainnya',
                    'units' => $units,
                    'revenue' => $revenue,
                    'worker_cost' => $worker,
                    'margin' => $revenue - $worker,
                ];
            })
            ->values()
            ->all();
    }
}
