<?php

return [

    'sales' => [
        /** URL SAS HTTPS por defecto (Azure Data Lake Gen2). */
        'adls_default_url' => env('SALES_ADLS_URL'),

        /** Hosts permitidos para descarga remota (coma-separados). */
        'adls_allowed_hosts' => array_values(array_filter(array_map(
            'trim',
            explode(',', (string) env(
                'SALES_ADLS_ALLOWED_HOSTS',
                '.dfs.core.windows.net,.blob.core.windows.net'
            ))
        ))),

        'download_timeout_seconds' => (int) env('SALES_ADLS_TIMEOUT', 120),
    ],

];
