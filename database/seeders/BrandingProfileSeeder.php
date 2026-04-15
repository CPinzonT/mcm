<?php

namespace Database\Seeders;

use App\Models\BrandingProfile;
use Illuminate\Database\Seeder;

class BrandingProfileSeeder extends Seeder
{
    public function run(): void
    {
        BrandingProfile::firstOrCreate(
            ['name' => 'Corporativo MCM'],
            [
                'is_default'      => true,
                'company_name'    => 'MCM — Gestión de Cartera',
                'primary_color'   => '#1e3a5f',
                'secondary_color' => '#2563eb',
                'accent_color'    => '#f59e0b',
                'font_family'     => 'Inter',
                'header_text'     => 'Informe de Cartera y Recaudos',
                'footer_text'     => 'Documento confidencial. Generado por Cartera Project.',
                'address'         => null,
                'phone'           => null,
                'email'           => null,
                'website'         => null,
            ]
        );
    }
}
