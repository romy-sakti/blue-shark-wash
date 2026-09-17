<?php

namespace App\Filament\Support;

use Filament\Forms\Components\TextInput;

class MoneyInput
{
    /**
     * Input uang dengan pemisah ribuan Indonesia: 10.000
     */
    public static function make(string $name): TextInput
    {
        $formatJs = '$el.value = $el.value.replace(/\\D/g, \'\').replace(/\\B(?=(\\d{3})+(?!\\d))/g, \'.\')';

        return TextInput::make($name)
            ->prefix('Rp')
            ->placeholder('10.000')
            ->inputMode('numeric')
            ->autocomplete('off')
            ->extraAlpineAttributes([
                'x-init' => $formatJs,
                'x-on:input' => $formatJs,
                'x-on:blur' => $formatJs,
            ])
            ->formatStateUsing(function ($state) {
                if ($state === null || $state === '') {
                    return $state;
                }

                return format_angka($state);
            })
            ->dehydrateStateUsing(fn ($state) => parse_rupiah($state));
    }
}
