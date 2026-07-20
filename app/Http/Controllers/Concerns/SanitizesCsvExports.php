<?php

namespace App\Http\Controllers\Concerns;

trait SanitizesCsvExports
{
    protected function sanitizeExportRow(array $values): array
    {
        return array_map(fn ($value) => $this->sanitizeExportValue($value), $values);
    }

    protected function sanitizeExportValue(mixed $value): string
    {
        $string = trim((string) ($value ?? '-'));

        if ($string === '') {
            return '-';
        }

        if (preg_match('/^[=\-+@]/', $string) === 1) {
            return "'".$string;
        }

        return preg_replace("/[\r\n\t]+/", ' ', $string) ?? $string;
    }
}
