<?php

declare(strict_types=1);

namespace Zoosper\Grid\Tests\Unit;

use Zoosper\Grid\GridColumn;
use Zoosper\Grid\GridCsvExporter;
use Zoosper\Grid\GridDefinition;

function securityExportDefinition(): GridDefinition
{
    return new GridDefinition('Security export', [
        new GridColumn('value', 'Value'),
    ]);
}

/** @return list<list<string|null>> */
function parseSecurityCsv(string $csv): array
{
    $stream = fopen('php://temp', 'w+b');
    if ($stream === false) {
        throw new \RuntimeException('Unable to open test CSV stream.');
    }

    try {
        fwrite($stream, $csv);
        rewind($stream);
        $rows = [];
        while (($row = fgetcsv($stream, escape: '')) !== false) {
            $rows[] = $row;
        }
        return $rows;
    } finally {
        fclose($stream);
    }
}

test('CSV export neutralises spreadsheet formula prefixes', function (): void {
    $values = [
        '=2+2',
        '+SUM(A1:A2)',
        '-10+20',
        '@SUM(A1:A2)',
        "\t=2+2",
        "\r=2+2",
        "\n=2+2",
    ];
    $csv = (new GridCsvExporter())->export(
        securityExportDefinition(),
        array_map(static fn (string $value): array => ['value' => $value], $values),
        ['value'],
    );
    $rows = parseSecurityCsv($csv);

    expect(array_column(array_slice($rows, 1), 0))->toBe([
        "'=2+2",
        "'+SUM(A1:A2)",
        "'-10+20",
        "'@SUM(A1:A2)",
        "'\t=2+2",
        "'\r=2+2",
        "'\n=2+2",
    ]);
});

test('CSV export preserves ordinary scalar values', function (): void {
    $csv = (new GridCsvExporter())->export(
        securityExportDefinition(),
        [
            ['value' => 'Normal text'],
            ['value' => 42],
            ['value' => true],
            ['value' => null],
        ],
        ['value'],
    );
    $rows = parseSecurityCsv($csv);

    expect($rows)->toBe([
        ['Value'],
        ['Normal text'],
        ['42'],
        ['1'],
        [null],
    ]);
});

test('CSV export neutralises a contributed column label', function (): void {
    $definition = new GridDefinition('Security export', [
        new GridColumn('value', '=WEBSERVICE("https://example.invalid")'),
    ]);
    $rows = parseSecurityCsv(
        (new GridCsvExporter())->export($definition, [['value' => 'safe']], ['value']),
    );

    expect($rows[0][0])->toBe("'=WEBSERVICE(\"https://example.invalid\")");
    expect($rows[1][0])->toBe('safe');
});











