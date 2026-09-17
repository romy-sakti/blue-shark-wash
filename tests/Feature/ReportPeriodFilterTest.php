<?php

namespace Tests\Feature;

use App\Filament\Pages\LaporanBulanan;
use App\Filament\Pages\LaporanHarian;
use App\Filament\Pages\LaporanMingguan;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Livewire\Livewire;
use Tests\TestCase;

class ReportPeriodFilterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        if (! extension_loaded('pdo_sqlite')) {
            $this->markTestSkipped('Ekstensi pdo_sqlite tidak tersedia di environment ini.');
        }

        parent::setUp();

        Filament::setCurrentPanel(Filament::getPanel('admin'));
        Carbon::setTestNow(Carbon::parse('2026-09-16 10:00:00', 'Asia/Jakarta'));
    }

    public function test_laporan_harian_hanya_satu_hari(): void
    {
        $this->actingAs(User::factory()->create());

        $component = Livewire::test(LaporanHarian::class);

        $component->assertSee('Tanggal')
            ->assertDontSee('Dari')
            ->assertDontSee('Sampai');

        $this->assertTrue($component->instance()->reportFrom()->isSameDay('2026-09-16'));
        $this->assertTrue($component->instance()->reportUntil()->isSameDay('2026-09-16'));
        $this->assertSame(0, $component->instance()->reportFrom()->diffInDays($component->instance()->reportUntil()));
    }

    public function test_laporan_mingguan_memakai_senin_sampai_minggu(): void
    {
        $this->actingAs(User::factory()->create());

        $component = Livewire::test(LaporanMingguan::class);

        $component->assertSee('Minggu')
            ->assertSee('Senin–Minggu')
            ->assertDontSee('Dari')
            ->assertDontSee('Sampai');

        $this->assertSame('2026-09-14', $component->instance()->reportFrom()->toDateString());
        $this->assertSame('2026-09-20', $component->instance()->reportUntil()->toDateString());

        $component->set('data.date', '2026-09-09')
            ->assertSet('data.date', '2026-09-07');
    }

    public function test_laporan_bulanan_memakai_satu_bulan_kalender(): void
    {
        $this->actingAs(User::factory()->create());

        $component = Livewire::test(LaporanBulanan::class);

        $component->assertSee('Bulan')
            ->assertSee('September 2026')
            ->assertDontSee('Dari')
            ->assertDontSee('Sampai');

        $this->assertSame('2026-09-01', $component->instance()->reportFrom()->toDateString());
        $this->assertSame('2026-09-30', $component->instance()->reportUntil()->toDateString());
        $this->assertSame('September 2026', $component->instance()->reportPeriodLabel());
    }
}
