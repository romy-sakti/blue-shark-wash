<?php

namespace Tests\Unit;

use App\Support\OperatingDays;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\TestCase;

class OperatingDaysTest extends TestCase
{
    public function test_it_averages_by_operating_days_not_calendar_days(): void
    {
        $this->assertSame(0, OperatingDays::average(150000, 0));
        $this->assertSame(10000, OperatingDays::average(150000, 15));
        $this->assertNotSame(OperatingDays::average(150000, 15), OperatingDays::average(150000, 31));
        $this->assertSame(9.7, OperatingDays::averageDecimal(145, 15));
    }

    public function test_it_counts_unique_bookkeeping_days(): void
    {
        $summaries = collect([
            (object) ['period_date' => Carbon::parse('2026-08-17')],
            (object) ['period_date' => Carbon::parse('2026-08-17')],
            (object) ['period_date' => Carbon::parse('2026-08-18')],
        ]);

        $this->assertSame(2, OperatingDays::count($summaries));
        $this->assertSame(31, OperatingDays::calendarCount(
            Carbon::parse('2026-08-01'),
            Carbon::parse('2026-08-31'),
        ));
    }

    public function test_it_labels_partial_first_month(): void
    {
        $summaries = collect([
            (object) ['period_date' => Carbon::parse('2026-08-17')],
            (object) ['period_date' => Carbon::parse('2026-08-31')],
        ]);

        $label = OperatingDays::label($summaries, 2, 31);

        $this->assertStringContainsString('2 hari operasi', $label);
        $this->assertStringContainsString('dari 31 hari kalender', $label);
    }
}
