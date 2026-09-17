<?php

namespace App\Enums;

use Filament\Support\Contracts\HasDescription;
use Filament\Support\Contracts\HasLabel;

enum ExpenseAllocation: string implements HasDescription, HasLabel
{
    case Daily = 'daily';
    case Period = 'period';

    public function getLabel(): string
    {
        return match ($this) {
            self::Daily => 'Harian',
            self::Period => 'Bulanan / periode',
        };
    }

    public function getDescription(): string
    {
        return match ($this) {
            self::Daily => 'Masuk laporan harian pada tanggal pengeluaran, lalu terakumulasi ke minggu/bulan.',
            self::Period => 'Hanya masuk laporan bulan yang dipilih (contoh: listrik, PDAM). Tidak membebani satu hari operasional.',
        };
    }
}
