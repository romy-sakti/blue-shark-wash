<?php

namespace App\Filament\Auth;

use App\Services\AuditLogger;
use Filament\Http\Responses\Auth\Contracts\LoginResponse;
use Filament\Pages\Auth\Login as BaseLogin;
use Illuminate\Contracts\Support\Htmlable;

class Login extends BaseLogin
{
    public function getTitle(): string|Htmlable
    {
        return 'Masuk · Blue Shark Wash';
    }

    public function getHeading(): string|Htmlable
    {
        return 'Masuk';
    }

    public function getSubheading(): string|Htmlable|null
    {
        return 'Pembukuan cuci mobil & motor';
    }

    public function authenticate(): ?LoginResponse
    {
        $response = parent::authenticate();

        if ($response !== null && ($user = filament()->auth()->user())) {
            app(AuditLogger::class)->log($user, 'logged_in');
        }

        return $response;
    }
}
