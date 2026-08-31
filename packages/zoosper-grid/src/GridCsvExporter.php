<?php

declare(strict_types=1);

namespace Zoosper\Grid;

use RuntimeException;

/**
 * Converts GridDefinition-backed rows into standards-compliant CSV.
 *
 * Spreadsheet applications may interpret cells beginning with formula control
 * characters as executable formulas. Every exported scalar therefore passes
 * through neutraliseFormula() before fputcsv() performs delimiter escaping.
 */
final readonly class GridCsvExporter
{
    /**
     * @param iterable<array<string, mixed>> $rows
     * @param list<string>|null $visibleColumnKeys
     */
    public function export(
        GridDefinition $definition,
        iterable $rows,
        ?array $visibleColumnKeys = null,
    ): string {
        $columns = $this->exportableColumns($definition, $visibleColumnKeys);
        $stream = fopen('php://temp', 'w+b');
        if ($stream === false) {
            throw new RuntimeException('Unable to open temporary CSV stream.');
        }

        try {
            fputcsv(
                $stream,
                array_map(
                    static fn (GridColumn $column): string => self::neutraliseFormula($column->label),
                    $columns,
                ),
                escape: '',
            );

            foreach ($rows as $row) {
                fputcsv(
                    $stream,
                    array_map(
                        static fn (GridColumn $column): string => self::neutraliseFormula(
                            self::scalarValue($row[$column->key] ?? null),
                        ),
                        $columns,
                    ),
                    escape: '',
                );
            }

            rewind($stream);
            $csv = stream_get_contents($stream);
            if ($csv === false) {
                throw new RuntimeException('Unable to read generated CSV stream.');
            }

            return $csv;
        } finally {
            fclose($stream);
        }
    }

    /**
     * @param list<string>|null $visibleColumnKeys
     * @return list<GridColumn>
     */
    private function exportableColumns(
        GridDefinition $definition,
        ?array $visibleColumnKeys,
    ): array {
        $allowed = $visibleColumnKeys ?? $definition->defaultVisibleColumnKeys();
        $allowedLookup = array_fill_keys($allowed, true);

        return array_values(array_filter(
            $definition->columns,
            static fn (GridColumn $column): bool => $column->key !== 'actions'
                && isset($allowedLookup[$column->key]),
        ));
    }

    private static function scalarValue(mixed $value): string
    {
        if ($value === null) {
            return '';
        }
        if (is_bool($value)) {
            return $value ? '1' : '0';
        }
        if (is_scalar($value)) {
            return (string) $value;
        }

        return json_encode(
            $value,
            JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE,
        );
    }

    /**
     * Prevent spreadsheet formula execution while preserving the displayed
     * value. A leading apostrophe is the conventional literal-text marker used
     * by spreadsheet applications and remains visible in raw CSV consumers.
     */
    private static function neutraliseFormula(string $value): string
    {
        if ($value === '') {
            return '';
        }

        $first = $value[0];
        if (in_array($first, ['=', '+', '-', '@', "\t", "\r", "\n"], true)) {
            return "'" . $value;
        }

        return $value;
    }
}











