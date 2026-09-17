<?php

namespace Tests\Unit;

use App\Models\AuditLog;
use App\Models\BookkeepingPeriod;
use Tests\TestCase;

class AuditLogDisplayTest extends TestCase
{
    public function test_it_summarizes_bookkeeping_in_indonesian(): void
    {
        $log = new AuditLog([
            'action' => 'created',
            'auditable_type' => BookkeepingPeriod::class,
            'new_values' => [
                'period_date' => '2026-09-15',
                'summary' => [
                    'actual_revenue' => 153000,
                    'net_profit' => 83000,
                ],
            ],
        ]);

        $this->assertSame('Mencatat', $log->activityLabel());
        $this->assertSame('Pembukuan', $log->subjectLabel());
        $this->assertStringContainsString('15 September 2026', $log->summary());
        $this->assertStringContainsString('Rp 153.000', $log->summary());
    }

    public function test_it_formats_payloads_without_raw_json(): void
    {
        $rows = AuditLog::displayValues([
            'status' => 'locked',
            'allocation' => 'daily',
            'amount' => 20000,
            'items' => [
                ['custom_name' => 'Paket 1', 'quantity' => 3, 'actual_revenue' => 39000],
            ],
        ]);

        $this->assertSame('Terkunci', $rows['Status']);
        $this->assertSame('Harian', $rows['Pembebanan']);
        $this->assertSame('Rp 20.000', $rows['Nominal']);
        $this->assertStringContainsString('Paket 1 × 3', $rows['Rincian layanan']);
        $this->assertStringContainsString('Rp 39.000', $rows['Rincian layanan']);
    }
}
