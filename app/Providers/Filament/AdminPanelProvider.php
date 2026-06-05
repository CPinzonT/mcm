<?php

namespace App\Providers\Filament;

use App\Filament\Auth\Responses\LoginResponse as CustomLoginResponse;
use App\Filament\Pages\Auth\Login;
use App\Filament\Pages\Dashboard;
use App\Filament\Pages\StrategicDashboard;
use App\Models\BrandingProfile;
use Filament\Auth\Http\Responses\Contracts\LoginResponse as LoginResponseContract;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Filament\Widgets\AccountWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Support\Facades\Schema;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function register(): void
    {
        parent::register();

        $this->app->bind(LoginResponseContract::class, CustomLoginResponse::class);
    }

    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login(Login::class)
            ->homeUrl(fn (): string => StrategicDashboard::getUrl(panel: 'admin'))
            ->brandName(fn (): string => $this->resolveBrandName())
            ->brandLogo(fn (): ?string => $this->resolveBrandLogo())
            ->darkModeBrandLogo(fn (): ?string => $this->resolveBrandLogo())
            ->brandLogoHeight('2.35rem')
            ->colors([
                'primary' => Color::hex('#2852a0'),
                'gray'    => Color::Slate,
                'info'    => Color::hex('#5b9bd5'),
                'success' => Color::hex('#22C55E'),
                'warning' => Color::hex('#F59E0B'),
                'danger'  => Color::hex('#EF4444'),
            ])
            ->navigationGroups([
                NavigationGroup::make('Principal'),
                NavigationGroup::make('Operación')->collapsible(),
                NavigationGroup::make('Inteligencia')->collapsible(),
                NavigationGroup::make('Administración')->collapsible(),
                NavigationGroup::make('Configuración')->collapsible(),
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->pages([Dashboard::class, StrategicDashboard::class])
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                AccountWidget::class,
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
            ->renderHook(
                PanelsRenderHook::STYLES_AFTER,
                fn (): string => view('filament.partials.mcm-panel-theme')->render(),
            );
    }

    private function resolveBrandName(): string
    {
        return $this->getBrandingProfile()?->company_name ?: 'Cartera Project';
    }

    private function resolveBrandLogo(): ?string
    {
        $logoPath = $this->getBrandingProfile()?->logo_path;

        if (!$logoPath) {
            return asset('images/mcm-logo.svg');
        }

        if (str_starts_with($logoPath, 'http://') || str_starts_with($logoPath, 'https://')) {
            return $logoPath;
        }

        if (str_starts_with($logoPath, '/storage/')) {
            return $logoPath;
        }

        return asset('storage/' . ltrim($logoPath, '/'));
    }

    private function getBrandingProfile(): ?BrandingProfile
    {
        try {
            if (!Schema::hasTable('branding_profiles')) {
                return null;
            }

            return BrandingProfile::getDefault();
        } catch (\Throwable) {
            return null;
        }
    }
}
