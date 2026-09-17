<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum AdjustmentDirection: string implements HasLabel
{
    case Increase = 'increase';
    case Decrease = 'decrease';

    public function getLabel(): string
    {
        return match ($this) {
            self::Increase => 'Menambah pendapatan',
            self::Decrease => 'Mengurangi pendapatan',
        };
    }
}
