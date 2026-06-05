<?php

namespace App\Services\Loads\Support;

use Carbon\CarbonImmutable;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class ImportNormalizer
{
    private const MONTH_NAME_MAP = [
        'enero' => '01',
        'ene' => '01',
        'febrero' => '02',
        'feb' => '02',
        'marzo' => '03',
        'mar' => '03',
        'abril' => '04',
        'abr' => '04',
        'mayo' => '05',
        'may' => '05',
        'junio' => '06',
        'jun' => '06',
        'julio' => '07',
        'jul' => '07',
        'agosto' => '08',
        'ago' => '08',
        'septiembre' => '09',
        'setiembre' => '09',
        'sept' => '09',
        'sep' => '09',
        'set' => '09',
        'octubre' => '10',
        'oct' => '10',
        'noviembre' => '11',
        'nov' => '11',
        'diciembre' => '12',
        'dic' => '12',
    ];

    public function normalizeHeader(?string $value): string
    {
        $normalized = Str::of((string) $value)
            ->trim()
            ->lower()
            ->ascii()
            ->replaceMatches('/[^a-z0-9]+/', '_')
            ->trim('_')
            ->value();

        return $normalized;
    }

    public function normalizeText(mixed $value, ?int $maxLength = null): ?string
    {
        $text = trim((string) ($value ?? ''));

        if ($text === '') {
            return null;
        }

        $text = preg_replace('/\s+/u', ' ', $text) ?: $text;

        if ($maxLength !== null) {
            return Str::limit($text, $maxLength, '');
        }

        return $text;
    }

    public function normalizePhone(mixed $value, ?int $maxLength = null): ?string
    {
        $text = $this->normalizeText($value);

        if ($text === null) {
            return null;
        }

        $parts = preg_split('/\s*-\s*/', $text) ?: [$text];
        $primary = trim((string) ($parts[0] ?? ''));

        return $this->normalizeText($primary, $maxLength);
    }

    public function normalizeDocumentNumber(mixed $value): ?string
    {
        $text = $this->normalizeText($value, 100);

        return $text !== null ? Str::upper($text) : null;
    }

    /**
     * Comparación flexible de nombres (cliente / vendedor) entre recaudo y cartera.
     */
    public function namesMatch(?string $left, ?string $right): bool
    {
        if ($left === null || $left === '' || $right === null || $right === '') {
            return true;
        }

        $a = $this->normalizeMatchKey($left);
        $b = $this->normalizeMatchKey($right);

        if ($a === '' || $b === '') {
            return true;
        }

        return $a === $b || str_contains($a, $b) || str_contains($b, $a);
    }

    private function normalizeMatchKey(string $value): string
    {
        return Str::of($value)
            ->trim()
            ->upper()
            ->ascii()
            ->replaceMatches('/[^A-Z0-9]+/', '')
            ->value();
    }

    public function parseDate(mixed $value): ?CarbonImmutable
    {
        if ($value === null || $value === '') {
            return null;
        }

        if ($value instanceof \DateTimeInterface) {
            return CarbonImmutable::instance($value);
        }

        if (is_numeric($value)) {
            return CarbonImmutable::instance(ExcelDate::excelToDateTimeObject((float) $value));
        }

        $text = trim((string) $value);

        foreach ([
            'Y-m-d',
            'Y/m/d',
            'd/m/Y',
            'd-m-Y',
            'm/d/Y',
            'm-d-Y',
            'd.m.Y',
            'Ymd',
        ] as $format) {
            try {
                $date = CarbonImmutable::createFromFormat($format, $text);
            } catch (\Throwable) {
                $date = false;
            }

            if ($date !== false) {
                return $date;
            }
        }

        try {
            return CarbonImmutable::parse($text);
        } catch (\Throwable) {
            return null;
        }
    }

    public function parseMonthKey(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $text = trim((string) $value);

        if (preg_match('/^(?<year>\d{4})[-\/](?<month>\d{1,2})$/', $text, $matches)) {
            return sprintf('%04d-%02d', (int) $matches['year'], (int) $matches['month']);
        }

        if (preg_match('/^(?<month>\d{1,2})[-\/](?<year>\d{4})$/', $text, $matches)) {
            return sprintf('%04d-%02d', (int) $matches['year'], (int) $matches['month']);
        }

        $date = $this->parseDate($value);

        return $date?->format('Y-m');
    }

    public function firstDayOfPeriod(string $periodKey): CarbonImmutable
    {
        return CarbonImmutable::createFromFormat('Y-m-d', "{$periodKey}-01");
    }

    public function parseNumber(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            return (float) $value;
        }

        $text = trim((string) $value);
        $text = preg_replace('/[^\d,\.\-\(\)]/', '', $text) ?? '';

        if ($text === '') {
            return null;
        }

        $negative = false;

        if (str_starts_with($text, '(') && str_ends_with($text, ')')) {
            $negative = true;
            $text = trim($text, '()');
        }

        $commaCount = substr_count($text, ',');
        $dotCount = substr_count($text, '.');

        if ($commaCount > 0 && $dotCount > 0) {
            $lastComma = strrpos($text, ',');
            $lastDot = strrpos($text, '.');

            if ($lastComma > $lastDot) {
                $text = str_replace('.', '', $text);
                $text = str_replace(',', '.', $text);
            } else {
                $text = str_replace(',', '', $text);
            }
        } elseif ($commaCount > 0) {
            $segments = explode(',', $text);
            $decimalPart = end($segments);

            if (strlen((string) $decimalPart) <= 2) {
                $text = str_replace('.', '', $text);
                $text = str_replace(',', '.', $text);
            } else {
                $text = str_replace(',', '', $text);
            }
        } else {
            $segments = explode('.', $text);
            $decimalPart = end($segments);

            if ($dotCount > 1 || strlen((string) $decimalPart) > 2) {
                $text = str_replace('.', '', $text);
            }
        }

        if (! is_numeric($text)) {
            return null;
        }

        $number = (float) $text;

        return $negative ? $number * -1 : $number;
    }

    public function isEmptyRow(array $values): bool
    {
        foreach ($values as $value) {
            if (trim((string) ($value ?? '')) !== '') {
                return false;
            }
        }

        return true;
    }

    public function fileHash(string $path): string
    {
        return hash_file('sha256', $path);
    }

    public function calculateDaysOverdue(CarbonImmutable $dueDate, CarbonImmutable $cutDate): int
    {
        return app(\App\Services\Risk\RiskClassificationService::class)
            ->daysOverdueAfterDue($dueDate, $cutDate);
    }

    /**
     * @param  array<int, string>  $observedPeriods
     */
    public function inferPortfolioPeriodFromFilename(string $filename, array $observedPeriods = []): ?string
    {
        $normalized = Str::of(pathinfo($filename, PATHINFO_FILENAME))
            ->lower()
            ->ascii()
            ->replaceMatches('/[^a-z0-9]+/', ' ')
            ->trim()
            ->value();

        if ($normalized === '') {
            return null;
        }

        $month = null;

        foreach (self::MONTH_NAME_MAP as $token => $monthNumber) {
            if (preg_match('/(?:^|\s)' . preg_quote($token, '/') . '(?:\s|$)/', $normalized)) {
                $month = $monthNumber;
                break;
            }
        }

        if ($month === null) {
            return null;
        }

        if (preg_match('/(?:19|20)\d{2}/', $normalized, $matches)) {
            return sprintf('%04d-%s', (int) $matches[0], $month);
        }

        $matchingObserved = collect($observedPeriods)
            ->map(fn (string $periodKey) => $this->parseMonthKey($periodKey))
            ->filter()
            ->filter(fn (string $periodKey) => str_ends_with($periodKey, '-' . $month))
            ->sortDesc()
            ->values();

        return $matchingObserved->first();
    }

    /**
     * Ej. Recaudomcm_21demayo.xlsx → 2026-05 cuando el mes va pegado al día en el nombre.
     */
    public function inferPeriodFromFilename(string $filename, array $observedPeriods = []): ?string
    {
        $fromSpaced = $this->inferPortfolioPeriodFromFilename($filename, $observedPeriods);
        if ($fromSpaced !== null) {
            return $fromSpaced;
        }

        $normalized = Str::of(pathinfo($filename, PATHINFO_FILENAME))
            ->lower()
            ->ascii()
            ->replaceMatches('/[^a-z0-9]+/', '')
            ->value();

        if ($normalized === '') {
            return null;
        }

        $year = null;
        if (preg_match('/(20\d{2})/', $normalized, $yearMatch)) {
            $year = (int) $yearMatch[1];
        }

        foreach (self::MONTH_NAME_MAP as $token => $monthNumber) {
            if (! preg_match('/(\d{1,2})' . preg_quote($token, '/') . '/', $normalized)) {
                continue;
            }

            if ($year !== null) {
                return sprintf('%04d-%s', $year, $monthNumber);
            }

            $matchingObserved = collect($observedPeriods)
                ->map(fn (string $periodKey) => $this->parseMonthKey($periodKey))
                ->filter()
                ->filter(fn (string $periodKey) => str_ends_with($periodKey, '-' . $monthNumber))
                ->sortDesc()
                ->values();

            return $matchingObserved->first() ?? sprintf('%04d-%s', (int) now()->format('Y'), $monthNumber);
        }

        return null;
    }
}
