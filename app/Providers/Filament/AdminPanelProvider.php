<?php

namespace App\Providers\Filament;

use App\Filament\Pages\ChatInbox;
use App\Filament\Resources\QuickReplyResource;
use App\Filament\Resources\SiteBannerResource;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\HtmlString;
use Filament\Auth\MultiFactor\App\AppAuthentication;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
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
            ->login()
            ->colors(['primary' => Color::Blue])
            ->pages([
                ChatInbox::class,
            ])
            ->resources([
                QuickReplyResource::class,
                SiteBannerResource::class,
            ])
            ->renderHook(
                PanelsRenderHook::STYLES_BEFORE,
                fn (): HtmlString => new HtmlString(
                    '<link rel="stylesheet" href="' . Vite::asset('resources/css/filament-admin.css') . '">'
                ),
            )
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
            ->multiFactorAuthentication(
                AppAuthentication::make(),
                isRequired: true,
            )
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
