<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum PeriodStatus: string implements HasColor, HasLabel
{
    case Draft = 'draft';
    case Final = 'final';
    case Locked = 'locked';

    public function getLabel(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Final => 'Final',
            self::Locked => 'Terkunci',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Draft => 'gray',
            self::Final => 'success',
            self::Locked => 'danger',
        };
    }

    public function isLocked(): bool
    {
        return $this === self::Locked;
    }
}
