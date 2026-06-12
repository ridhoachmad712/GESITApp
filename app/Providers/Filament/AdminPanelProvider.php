<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Auth\Login;
use App\Filament\Resources\DocumentResource;
use App\Models\Category;
use App\Models\Setting;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationItem;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login(Login::class)
            ->profile()
            ->brandName(fn (): string => Setting::get('site_name').' — Panel Admin')
            ->brandLogo(fn (): ?string => Setting::get('logo_path')
                ? Storage::disk('public')->url(Setting::get('logo_path'))
                : null)
            ->brandLogoHeight('2.25rem')
            ->colors(fn (): array => [
                // Warna dasar dari Pengaturan Tampilan
                'primary' => Color::hex(Setting::get('primary_color') ?? '#1E3A8A'),
            ])
            // Urutan grup sidebar (Dashboard tanpa grup selalu paling atas)
            ->navigationGroups([
                'Manajemen Arsip',
                'Kategori Arsip',
                'Pengaturan',
            ])
            // Lazy via bootUsing: query kategori hanya saat panel admin diakses
            ->bootUsing(function (Panel $panel): void {
                $panel->navigationItems($this->categoryNavigationItems());
            })
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([])
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

    /**
     * Menu sidebar "Kategori Arsip": 9 kategori utama dari database,
     * masing-masing membuka daftar Dokumen terfilter kategori tersebut.
     *
     * @return array<NavigationItem>
     */
    private function categoryNavigationItems(): array
    {
        return rescue(function (): array {
            return Category::root()->get()
                ->map(fn (Category $category, int $index): NavigationItem => NavigationItem::make('kategori-'.$category->id)
                    ->label($category->name)
                    ->group('Kategori Arsip')
                    ->icon($category->icon ?: 'heroicon-o-folder')
                    ->sort(10 + $index)
                    ->url(DocumentResource::getUrl('index', [
                        'tableFilters' => ['kategori_utama' => ['value' => $category->id]],
                    ]))
                    ->isActiveWhen(fn (): bool => request()->routeIs('filament.admin.resources.documents.index')
                        && request()->input('tableFilters.kategori_utama.value') == $category->id))
                ->all();
        }, [], report: false);
    }
}
