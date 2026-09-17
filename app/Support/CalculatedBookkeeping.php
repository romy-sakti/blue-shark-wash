<?php

namespace App\Support;

/**
 * Hasil perhitungan satu periode pembukuan.
 *
 * Rumus (PRD §18):
 * Pendapatan Aktual = Harga Normal + Tambahan - Potongan
 * Margin             = Pendapatan Aktual - Biaya Pekerja
 * Laba Bersih        = Pendapatan Aktual - Biaya Pekerja - Biaya Operasional
 */
final class CalculatedBookkeeping
{
    /**
     * @param  array<int, array<string, mixed>>  $lines
     * @param  array<int, array<string, mixed>>  $vehicleBreakdown
     * @param  array<int, array<string, mixed>>  $serviceBreakdown
     */
    public function __construct(
        public int $totalUnits,
        public int $normalRevenue,
        public int $additionalIncome,
        public int $discount,
        public int $actualRevenue,
        public int $workerCostTotal,
        public int $operationalCost,
        public int $margin,
        public int $netProfit,
        public array $lines,
        public array $vehicleBreakdown,
        public array $serviceBreakdown,
    ) {}

    public static function empty(): self
    {
        return new self(0, 0, 0, 0, 0, 0, 0, 0, 0, [], [], []);
    }
}
