<?php

namespace App\Providers\Filament;

use App\Filament\Widgets\DashboardStats;
use App\Filament\Widgets\InventoryStatusChart;
use App\Filament\Widgets\PaymentsByMethodChart;
use App\Filament\Widgets\SalesByDayChart;
use App\Filament\Widgets\SetupChecklist;
use App\Http\Middleware\RedirectIfOnboardingIncomplete;
use App\Models\Organization;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\MenuItem;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class ManagerPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('manager')
            ->path('admin')
            ->tenant(Organization::class, slugAttribute: 'slug')
            ->login()
            ->colors([
                'primary' => Color::Orange,
            ])
            ->userMenuItems([
                MenuItem::make()
                    ->label('Open POS')
                    ->url(fn (): string => route('admin.open-pos')),
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                SetupChecklist::class,
                DashboardStats::class,
                SalesByDayChart::class,
                PaymentsByMethodChart::class,
                InventoryStatusChart::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->tenantMiddleware([
                RedirectIfOnboardingIncomplete::class,
            ], isPersistent: true)
            ->tenantMenuItems([
                MenuItem::make()
                    ->label('Open POS')
                    ->url(fn (): string => route('admin.open-pos')),
            ]);
    }
}
