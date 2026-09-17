<?php

namespace Tests\Unit;

use App\Services\BookkeepingCalculator;
use PHPUnit\Framework\TestCase;

class BookkeepingCalculatorTest extends TestCase
{
    public function test_prd_section_12_motor_packages(): void
    {
        $result = (new BookkeepingCalculator)->calculate([
            ['quantity' => 3, 'normal_price' => 13000, 'worker_cost' => 5000],
            ['quantity' => 4, 'normal_price' => 15000, 'worker_cost' => 5000],
            ['quantity' => 5, 'normal_price' => 18000, 'worker_cost' => 5000],
        ]);

        $this->assertSame(12, $result->totalUnits);
        $this->assertSame(189000, $result->normalRevenue);
        $this->assertSame(189000, $result->actualRevenue);
        $this->assertSame(60000, $result->workerCostTotal);
        $this->assertSame(129000, $result->margin);
    }

    public function test_prd_section_38_real_day_with_tip_and_operating_cost(): void
    {
        $result = (new BookkeepingCalculator)->calculate(
            [
                ['quantity' => 3, 'normal_price' => 13000, 'worker_cost' => 5000, 'vehicle_type_name' => 'Motor', 'service_name' => 'Paket 1'],
                ['quantity' => 2, 'normal_price' => 15000, 'worker_cost' => 5000, 'vehicle_type_name' => 'Motor', 'service_name' => 'Paket 2'],
                ['quantity' => 4, 'normal_price' => 18000, 'worker_cost' => 5000, 'vehicle_type_name' => 'Motor', 'service_name' => 'Paket 3'],
                ['quantity' => 1, 'normal_price' => 10000, 'worker_cost' => 5000, 'vehicle_type_name' => 'Lainnya', 'service_name' => 'Cuci Sepeda Listrik'],
            ],
            additionalIncome: 2000,
            discount: 0,
            operationalCost: 20000,
        );

        $this->assertSame(10, $result->totalUnits);
        $this->assertSame(151000, $result->normalRevenue);
        $this->assertSame(153000, $result->actualRevenue);
        $this->assertSame(50000, $result->workerCostTotal);
        $this->assertSame(103000, $result->margin);
        $this->assertSame(83000, $result->netProfit);
    }

    public function test_overpayment_does_not_change_package_price(): void
    {
        $result = (new BookkeepingCalculator)->calculate(
            [['quantity' => 1, 'normal_price' => 18000, 'worker_cost' => 5000]],
            additionalIncome: 2000,
        );

        $this->assertSame(18000, $result->normalRevenue);
        $this->assertSame(20000, $result->actualRevenue);
        $this->assertSame(5000, $result->workerCostTotal);
        $this->assertSame(15000, $result->margin);
        $this->assertSame(18000, $result->lines[0]['normal_price']);
    }

    public function test_underpayment_keeps_normal_price(): void
    {
        $result = (new BookkeepingCalculator)->calculate(
            [['quantity' => 1, 'normal_price' => 18000, 'worker_cost' => 5000]],
            discount: 3000,
        );

        $this->assertSame(18000, $result->normalRevenue);
        $this->assertSame(15000, $result->actualRevenue);
        $this->assertSame(10000, $result->margin);
    }

    public function test_custom_other_service_counts_as_income(): void
    {
        $result = (new BookkeepingCalculator)->calculate([
            ['quantity' => 1, 'normal_price' => 18000, 'worker_cost' => 5000, 'vehicle_type_name' => 'Motor', 'service_name' => 'Paket 3'],
            ['quantity' => 1, 'normal_price' => 10000, 'worker_cost' => 0, 'vehicle_type_name' => 'Lainnya', 'service_name' => 'Semir Body Motor'],
        ]);

        $this->assertSame(2, $result->totalUnits);
        $this->assertSame(28000, $result->normalRevenue);
        $this->assertSame(28000, $result->actualRevenue);
        $this->assertSame(5000, $result->workerCostTotal);
        $this->assertSame(23000, $result->margin);
    }
}
