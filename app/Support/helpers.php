<?php

use Illuminate\Support\Carbon;

if (! function_exists('parse_rupiah')) {
    /**
     * Terima angka bebas: 10000, 10.000, Rp 10.000
     */
    function parse_rupiah(int|float|string|null $value): int
    {
        if (is_int($value)) {
            return $value;
        }

        if (is_float($value)) {
            return (int) round($value);
        }

        $digits = preg_replace('/[^\d]/', '', (string) $value);

        return (int) ($digits === '' ? 0 : $digits);
    }
}

if (! function_exists('format_angka')) {
    /**
     * Format ribuan Indonesia, contoh: 10.000
     */
    function format_angka(int|float|string|null $amount): string
    {
        return number_format(parse_rupiah($amount), 0, ',', '.');
    }
}

if (! function_exists('rupiah')) {
    /**
     * Format nominal ke Rupiah tanpa desimal, contoh: Rp 18.000
     */
    function rupiah(int|float|string|null $amount): string
    {
        return 'Rp '.format_angka($amount);
    }
}

if (! function_exists('tanggal_id')) {
    /**
     * Format tanggal Indonesia, contoh: 15 September 2026
     */
    function tanggal_id(Carbon|string|null $date, string $format = 'd F Y'): string
    {
        if ($date === null) {
            return '—';
        }

        return Carbon::parse($date)->locale('id')->translatedFormat($format);
    }
}
