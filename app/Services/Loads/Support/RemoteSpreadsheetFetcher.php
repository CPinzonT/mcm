<?php

namespace App\Services\Loads\Support;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class RemoteSpreadsheetFetcher
{
    /**
     * @return array{storage_path: string, absolute_path: string, original_filename: string, file_hash: string}
     */
    public function fetch(string $url, string $directory = 'sales-loads'): array
    {
        $url = trim($url);

        if ($url === '' || ! filter_var($url, FILTER_VALIDATE_URL)) {
            throw new \DomainException('La URL de origen no es válida.');
        }

        $parts = parse_url($url);

        if (($parts['scheme'] ?? '') !== 'https') {
            throw new \DomainException('Solo se permiten URLs HTTPS para importar ventas.');
        }

        $host = strtolower((string) ($parts['host'] ?? ''));

        if (! $this->isAllowedHost($host)) {
            throw new \DomainException("El host «{$host}» no está autorizado para importación de ventas.");
        }

        $originalFilename = $this->resolveFilename($parts);
        $extension = strtolower(pathinfo($originalFilename, PATHINFO_EXTENSION));

        if (! in_array($extension, ['csv', 'xlsx', 'xls'], true)) {
            throw new \DomainException('El archivo remoto debe ser CSV, XLSX o XLS.');
        }

        $storagePath = trim($directory, '/') . '/' . now()->format('Ymd_His') . '_' . Str::lower(Str::random(8)) . '.' . $extension;
        $absolutePath = Storage::disk('local')->path($storagePath);

        Storage::disk('local')->makeDirectory(trim($directory, '/'));

        $response = Http::timeout((int) config('mcm.sales.download_timeout_seconds', 120))
            ->withOptions(['sink' => $absolutePath])
            ->get($url);

        if (! $response->successful()) {
            if (is_file($absolutePath)) {
                @unlink($absolutePath);
            }

            throw new \DomainException('No fue posible descargar el archivo desde Azure (HTTP ' . $response->status() . ').');
        }

        if (! is_file($absolutePath) || filesize($absolutePath) === 0) {
            throw new \DomainException('El archivo descargado está vacío.');
        }

        return [
            'storage_path' => $storagePath,
            'absolute_path' => $absolutePath,
            'original_filename' => $originalFilename,
            'file_hash' => hash_file('sha256', $absolutePath),
        ];
    }

    public function sanitizeSourceUrl(string $url): string
    {
        $parts = parse_url(trim($url));

        if ($parts === false) {
            return '';
        }

        $scheme = $parts['scheme'] ?? 'https';
        $host = $parts['host'] ?? '';
        $path = $parts['path'] ?? '';

        return rtrim("{$scheme}://{$host}{$path}", '/');
    }

    private function isAllowedHost(string $host): bool
    {
        $allowed = config('mcm.sales.adls_allowed_hosts', []);

        foreach ($allowed as $pattern) {
            $pattern = strtolower($pattern);

            if ($pattern === $host) {
                return true;
            }

            if (str_starts_with($pattern, '.') && str_ends_with($host, $pattern)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  array<string, mixed>  $parts
     */
    private function resolveFilename(array $parts): string
    {
        $path = (string) ($parts['path'] ?? '');
        $basename = basename($path);

        if ($basename !== '' && $basename !== '/') {
            return $basename;
        }

        return 'ventas_' . now()->format('Ymd_His') . '.xlsx';
    }
}
