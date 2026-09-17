<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Dashboard;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\FontProviders\LocalFontProvider;
use Filament\Navigation\NavigationGroup;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\HtmlString;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login(\App\Filament\Auth\Login::class)
            ->brandName('Blue Shark Wash')
            ->brandLogo(fn () => new HtmlString(view('filament.components.brand-mark')->render()))
            ->brandLogoHeight('auto')
            ->favicon(fn () => asset('images/logo.png'))
            ->font(
                'Inter',
                url: '/css/inter.css',
                provider: LocalFontProvider::class,
            )
            ->broadcasting(false)
            ->colors([
                'primary' => Color::hex('#0F766E'),
                'danger' => Color::Rose,
                'gray' => Color::Slate,
                'info' => Color::Cyan,
                'success' => Color::Teal,
                'warning' => Color::Amber,
            ])
            ->sidebarCollapsibleOnDesktop()
            ->navigationGroups([
                NavigationGroup::make('Pembukuan')->collapsed(false),
                NavigationGroup::make('Laporan'),
                NavigationGroup::make('Master Data'),
                NavigationGroup::make('Pengaturan'),
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([])
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn () => view('filament.hooks.print-styles'),
            )
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn () => view('filament.hooks.login-styles'),
            )
            ->renderHook(
                PanelsRenderHook::TOPBAR_START,
                fn () => view('filament.hooks.admin-topbar'),
            )
            ->renderHook(
                PanelsRenderHook::FOOTER,
                fn () => view('filament.hooks.login-footer'),
                scopes: \App\Filament\Auth\Login::class,
            )
            ->renderHook(
                PanelsRenderHook::FOOTER,
                fn () => view('filament.hooks.admin-footer'),
            )
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
