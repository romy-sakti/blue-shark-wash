<?php

namespace App\Support;

use Illuminate\Support\Carbon;

final class DateRange
{
    public function __construct(
        public Carbon $from,
        public Carbon $until,
        public string $label,
        public bool $includePeriodExpenses = false,
    ) {}

    public static function fromPreset(?string $preset, mixed $from = null, mixed $until = null): self
    {
        $preset = $preset ?: 'today';
        $now = now();

        return match ($preset) {
            'week' => new self(
                $now->copy()->startOfWeek(),
                $now->copy()->endOfWeek(),
                'Minggu ini',
                false,
            ),
            'month' => new self(
                $now->copy()->startOfMonth(),
                $now->copy()->endOfMonth(),
                'Bulan ini',
                true,
            ),
            'year' => new self(
                $now->copy()->startOfYear(),
                $now->copy()->endOfYear(),
                'Tahun ini',
                true,
            ),
            'custom' => new self(
                $from ? Carbon::parse($from)->startOfDay() : $now->copy()->startOfMonth(),
                $until ? Carbon::parse($until)->endOfDay() : $now->copy()->endOfDay(),
                'Kustom',
                true,
            ),
            default => new self(
                $now->copy()->startOfDay(),
                $now->copy()->endOfDay(),
                'Hari ini',
                false,
            ),
        };
    }

    /**
     * Jumlah hari kalender pada rentang (minimal 1).
     * Rata-rata laporan memakai hari operasi (ada pembukuan), bukan angka ini.
     */
    public function dayCount(): int
    {
        return max(1, $this->from->copy()->startOfDay()->diffInDays($this->until->copy()->startOfDay()) + 1);
    }
}
