<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\MasterDataSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookkeepingPeriodServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        if (! extension_loaded('pdo_sqlite')) {
            $this->markTestSkipped('Ekstensi pdo_sqlite tidak tersedia di environment ini.');
        }

        parent::setUp();
    }

    public function test_it_snapshots_prices_and_matches_prd_example(): void
    {
        $this->seed([UserSeeder::class, MasterDataSeeder::class]);

        $admin = User::query()->where('email', 'admin@blueshark.test')->first();
        $this->actingAs($admin);

        $services = \App\Models\Service::query()->with('vehicleType')->get()
            ->mapWithKeys(fn ($service) => [$service->vehicleType->name.'|'.$service->name => $service->id]);

        $period = app(\App\Services\BookkeepingPeriodService::class)->save([
            'period_date' => '2026-09-15',
            'quantities' => [
                $services['Motor|Paket 1'] => 3,
                $services['Motor|Paket 2'] => 2,
                $services['Motor|Paket 3'] => 4,
            ],
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
            'notes' => 'Tes PRD 38',
            'daily_expenses' => [
                [
                    'expense_category_id' => \App\Models\ExpenseCategory::query()->where('name', 'Bahan Cuci')->value('id'),
                    'name' => 'Shampo',
                    'amount' => 20000,
                ],
            ],
        ], $admin);

        $summary = $period->summary()->first();

        $this->assertSame(10, $summary->total_units);
        $this->assertSame(151000, $summary->normal_revenue);
        $this->assertSame(153000, $summary->actual_revenue);
        $this->assertSame(50000, $summary->worker_cost_total);
        $this->assertSame(20000, $summary->operational_cost);
        $this->assertSame(103000, $summary->margin);
        $this->assertSame(83000, $summary->net_profit);

        $paket3 = $period->items()->where('service_id', $services['Motor|Paket 3'])->first();
        $this->assertSame(18000, $paket3->normal_price);
        $this->assertSame(5000, $paket3->worker_cost);

        $custom = $period->items()->where('is_custom', true)->first();
        $this->assertSame('Cuci Sepeda Listrik', $custom->custom_name);
        $this->assertNull($custom->vehicle_type_id);
        $this->assertSame(10000, $custom->actual_revenue);

        $log = \App\Models\AuditLog::query()
            ->where('auditable_id', $period->id)
            ->where('action', 'created')
            ->latest('id')
            ->first();

        $this->assertNotNull($log);
        $this->assertSame(153000, $log->new_values['summary']['actual_revenue']);
        $this->assertSame(83000, $log->new_values['summary']['net_profit']);
        $this->assertStringContainsString('Pembukuan', $log->summary());
    }
}
