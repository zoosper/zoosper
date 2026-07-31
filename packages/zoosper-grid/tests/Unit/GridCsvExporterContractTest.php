<?php

declare(strict_types=1);

namespace Zoosper\Grid\Tests\Unit;

test('CSV exporter keeps formula neutralisation inside the package boundary', function (): void {
    $source = (string) file_get_contents(dirname(__DIR__, 2) . '/src/GridCsvExporter.php');

    expect($source)->toContain('private static function neutraliseFormula(string $value): string')
        ->toContain("['=', '+', '-', '@', \"\\t\", \"\\r\", \"\\n\"]")
        ->toContain('self::neutraliseFormula(');
});
