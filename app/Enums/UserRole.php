<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum UserRole: string implements HasColor, HasLabel
{
    case Owner = 'owner';
    case Admin = 'admin';

    public function getLabel(): string
    {
        return match ($this) {
            self::Owner => 'Owner',
            self::Admin => 'Admin',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Owner => 'warning',
            self::Admin => 'info',
        };
    }
}
