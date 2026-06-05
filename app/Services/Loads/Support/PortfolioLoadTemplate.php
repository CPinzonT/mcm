<?php

namespace App\Services\Loads\Support;

/**
 * Encabezados oficiales del archivo de cartera (layout SAP / mayo 2026).
 */
final class PortfolioLoadTemplate
{
    /** @return string[] */
    public static function headers(): array
    {
        return [
            '#',
            'Cuenta',
            'Cliente',
            'NIT',
            'Direccion',
            'Contacto',
            'Telefono',
            'Canal',
            'Vendedor',
            'Regional',
            'NroDocumento',
            'RefDocumento',
            'TipoDocumento',
            'FechaContabilizacion',
            'FechaVencimiento',
            'ValorDocumento',
            'ImportePendiente',
            'CRC_ValorARecaudar',
            'MARCASMCM_ValorARecaudar',
            'MARCASPRIVADAS_ValorARecaudar',
            'NOAPLICA_ValorARecaudar',
            'DiasVencido',
            'Actual',
            '1-30Dias',
            '31-60Dias',
            '61-90Dias',
            '91-180Dias',
            '181-360Dias',
            '+360Dias',
        ];
    }
}
