<?php

namespace App\Filament\Pages;

use App\Models\BrandingProfile;
use App\Models\RiskLevelSetting;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\WithFileUploads;

class SystemSettingsPage extends Page
{
    use WithFileUploads;

    protected string $view = 'filament.pages.system-settings-page';

    protected static string|\BackedEnum|null $navigationIcon  = 'heroicon-o-cog-6-tooth';
    protected static string|\UnitEnum|null   $navigationGroup = 'Configuración';
    protected static ?string $navigationLabel = 'General del sistema';
    protected static ?string $title           = 'General del Sistema';
    protected static ?int    $navigationSort  = 1;

    public array $settings = [
        'company_name'    => '',
        'currency'        => 'COP',
        'phone'           => '',
        'email'           => '',
        'address'         => '',
        'website'         => '',
        'logo_path'       => null,
        'primary_color'   => '#2563eb',
        'accent_color'    => '#3b82f6',
        'secondary_color' => '#64748b',
        'header_text'     => '',
        'footer_text'     => '',
    ];

    public $logoFile = null;

    public function mount(): void
    {
        $profile = BrandingProfile::getDefault();

        if ($profile) {
            $this->settings = [
                'company_name'    => $profile->company_name    ?? '',
                'currency'        => $profile->currency        ?? 'COP',
                'phone'           => $profile->phone           ?? '',
                'email'           => $profile->email           ?? '',
                'address'         => $profile->address         ?? '',
                'website'         => $profile->website         ?? '',
                'logo_path'       => $profile->logo_path,
                'primary_color'   => $profile->primary_color   ?? '#2563eb',
                'accent_color'    => $profile->accent_color    ?? '#3b82f6',
                'secondary_color' => $profile->secondary_color ?? '#64748b',
                'header_text'     => $profile->header_text     ?? '',
                'footer_text'     => $profile->footer_text     ?? '',
            ];
        }
    }

    public function getHeading(): string|\Illuminate\Contracts\Support\Htmlable|null
    {
        return null;
    }

    public static function canAccess(): bool
    {
        return Auth::user()?->can('manage system config') ?? false;
    }

    #[Computed]
    public function riskLevels()
    {
        return RiskLevelSetting::orderBy('order')->get();
    }

    #[Computed]
    public function logoPreviewUrl(): ?string
    {
        if ($this->logoFile) {
            try {
                return $this->logoFile->temporaryUrl();
            } catch (\Throwable) {
                // Fallback to persisted logo when temporary preview is unavailable.
            }
        }

        $logoPath = $this->settings['logo_path'] ?? null;

        if (!$logoPath) {
            return null;
        }

        if (str_starts_with($logoPath, 'http://') || str_starts_with($logoPath, 'https://')) {
            return $logoPath;
        }

        if (str_starts_with($logoPath, '/storage/')) {
            return $logoPath;
        }

        return asset('storage/' . ltrim($logoPath, '/'));
    }

    public function saveSettings(): void
    {
        $this->validate([
            'logoFile'              => 'nullable|image|max:2048',
            'settings.company_name' => 'nullable|string|max:255',
            'settings.email'        => 'nullable|email|max:255',
            'settings.phone'        => 'nullable|string|max:60',
            'settings.address'      => 'nullable|string|max:500',
            'settings.website'      => 'nullable|url|max:255',
            'settings.currency'     => 'nullable|string|max:10',
            'settings.primary_color'   => 'nullable|regex:/^#[0-9a-fA-F]{6}$/',
            'settings.accent_color'    => 'nullable|regex:/^#[0-9a-fA-F]{6}$/',
            'settings.secondary_color' => 'nullable|regex:/^#[0-9a-fA-F]{6}$/',
            'settings.header_text'  => 'nullable|string|max:2000',
            'settings.footer_text'  => 'nullable|string|max:2000',
        ]);

        if ($this->logoFile) {
            $path = $this->logoFile->store('logos', 'public');
            $this->settings['logo_path'] = $path;
            $this->logoFile = null;
        }

        $data = [
            'company_name'    => $this->settings['company_name'],
            'phone'           => $this->settings['phone'],
            'email'           => $this->settings['email'],
            'address'         => $this->settings['address'],
            'website'         => $this->settings['website'],
            'primary_color'   => $this->settings['primary_color'],
            'accent_color'    => $this->settings['accent_color'],
            'secondary_color' => $this->settings['secondary_color'],
            'header_text'     => $this->settings['header_text'],
            'footer_text'     => $this->settings['footer_text'],
            'logo_path'       => $this->settings['logo_path'],
        ];

        $profile = BrandingProfile::getDefault();

        if ($profile) {
            $profile->update($data);
        } else {
            BrandingProfile::create(array_merge($data, [
                'name'       => 'default',
                'is_default' => true,
            ]));
        }

        Notification::make()->title('Configuración guardada')->success()->send();
    }
}
