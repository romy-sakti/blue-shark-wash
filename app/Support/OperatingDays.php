<?php

namespace App\Support;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

final class OperatingDays
{
    /**
     * Jumlah hari yang ada pembukuan (bukan hari kalender).
     */
    public static function count(Collection $summaries): int
    {
        return $summaries
            ->map(function ($row) {
                $date = data_get($row, 'period_date');

                return $date instanceof Carbon
                    ? $date->toDateString()
                    : ($date ? Carbon::parse($date)->toDateString() : null);
            })
            ->filter()
            ->unique()
            ->count();
    }

    public static function calendarCount(Carbon $from, Carbon $until): int
    {
        return max(1, $from->copy()->startOfDay()->diffInDays($until->copy()->startOfDay()) + 1);
    }

    public static function average(int $total, int $operatingDays): int
    {
        return $operatingDays > 0 ? (int) round($total / $operatingDays) : 0;
    }

    public static function averageDecimal(int|float $total, int $operatingDays, int $precision = 1): float
    {
        return $operatingDays > 0 ? round($total / $operatingDays, $precision) : 0.0;
    }

    public static function label(Collection $summaries, int $operatingDays, int $calendarDays): string
    {
        if ($operatingDays === 0) {
            return '0 hari operasi';
        }

        $dates = $summaries
            ->map(fn ($row) => Carbon::parse(data_get($row, 'period_date')))
            ->sort()
            ->values();

        $first = $dates->first();
        $last = $dates->last();
        $span = $first->isSameDay($last)
            ? tanggal_id($first)
            : tanggal_id($first, 'd M').' – '.tanggal_id($last, 'd M Y');

        $label = $operatingDays === 1
            ? '1 hari operasi · '.$span
            : $operatingDays.' hari operasi · '.$span;

        if ($operatingDays < $calendarDays) {
            $label .= ' (dari '.$calendarDays.' hari kalender)';
        }

        return $label;
    }
}
