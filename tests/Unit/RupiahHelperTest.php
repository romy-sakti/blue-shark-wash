<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class RupiahHelperTest extends TestCase
{
    public function test_it_formats_thousands_with_dot(): void
    {
        $this->assertSame('10.000', format_angka(10000));
        $this->assertSame('10.000', format_angka('10000'));
        $this->assertSame('10.000', format_angka('10.000'));
        $this->assertSame('Rp 10.000', rupiah(10000));
        $this->assertSame('1.500.000', format_angka(1500000));
    }

    public function test_it_parses_formatted_and_raw_input(): void
    {
        $this->assertSame(10000, parse_rupiah('10.000'));
        $this->assertSame(10000, parse_rupiah('Rp 10.000'));
        $this->assertSame(10000, parse_rupiah('10000'));
        $this->assertSame(10000, parse_rupiah(10000));
    }
}
