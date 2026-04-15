<?php

namespace Database\Seeders;

use App\Models\SupportDocumentType;
use Illuminate\Database\Seeder;

class SupportDocumentTypesSeeder extends Seeder
{
    public function run(): void
    {
        // SUPUESTO FUNCIONAL: Tipos de soporte típicos para castigo DIAN.
        // Pendiente validación normativa con Dorian (F-05 del documento de requerimientos).
        $types = [
            ['code' => 'FACT_VENC',  'name' => 'Facturas Vencidas',          'required' => true,  'description' => 'Facturas originales objeto del castigo'],
            ['code' => 'CERT_MORA',  'name' => 'Certificado de Mora',         'required' => true,  'description' => 'Certificación del estado de mora del deudor'],
            ['code' => 'ACCION_COB', 'name' => 'Acciones de Cobro',           'required' => true,  'description' => 'Evidencia de gestiones de cobro realizadas'],
            ['code' => 'RUT_DEUD',   'name' => 'RUT del Deudor',              'required' => false, 'description' => 'Registro Único Tributario del deudor'],
            ['code' => 'DEMANDA',    'name' => 'Demanda o Proceso Jurídico',   'required' => false, 'description' => 'Documentos de proceso legal si aplica'],
            ['code' => 'ACUERDO',    'name' => 'Acuerdo de Pago Incumplido',  'required' => false, 'description' => 'Acuerdos de pago que no se cumplieron'],
            ['code' => 'CARTA_COB',  'name' => 'Carta de Cobro',              'required' => false, 'description' => 'Cartas formales de cobro enviadas al deudor'],
            ['code' => 'OTRO',       'name' => 'Otro Documento',              'required' => false, 'description' => 'Cualquier otro soporte relevante'],
        ];

        foreach ($types as $type) {
            SupportDocumentType::firstOrCreate(['code' => $type['code']], $type + ['active' => true]);
        }
    }
}
