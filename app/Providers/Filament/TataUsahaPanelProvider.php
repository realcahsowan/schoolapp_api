<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class TataUsahaPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('tataUsaha')
            ->path('tata-usaha')
            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(in: app_path('Filament/TataUsaha/Resources'), for: 'App\Filament\TataUsaha\Resources')
            ->discoverPages(in: app_path('Filament/TataUsaha/Pages'), for: 'App\Filament\TataUsaha\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/TataUsaha/Widgets'), for: 'App\Filament\TataUsaha\Widgets')
            ->widgets([
                AccountWidget::class,
                // FilamentInfoWidget::class,
            ])
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
            ])
            ->breadcrumbs(false)
            ->sidebarWidth('18rem')
            ->sidebarCollapsibleOnDesktop()
            ->darkMode(false)
            ->brandName('Tata Usaha')
            ->navigationItems([
                \Filament\Navigation\NavigationItem::make()
                    ->label('Kembali ke Portal')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->url('/administrasi-khusus')
                    ->sort(-1),
                \Filament\Navigation\NavigationItem::make()
                    ->label('Ganti Password')
                    ->icon('heroicon-o-key')
                    ->url('/password/change')
                    ->sort(0),
            ])
            ->tenant(\App\Models\School::class, 'alias')
            ->viteTheme('resources/css/filament/tataUsaha/theme.css');
    }
}
