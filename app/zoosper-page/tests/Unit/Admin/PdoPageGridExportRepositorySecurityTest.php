<?php

declare(strict_types=1);

namespace Zoosper\Page\Tests\Unit\Admin;

test('PDO export repository binds values and contains no request globals', function (): void {
    $source = (string) file_get_contents(
        dirname(__DIR__, 5) . '/app/zoosper-page/src/Admin/PdoPageGridExportRepository.php',
    );
    $code = preg_replace('#/\*\*.*?\*/#s', '', $source) ?? $source;

    expect($code)->toContain('bindValue(')
        ->toContain('PDO::PARAM_INT')
        ->toContain('PDO::PARAM_STR')
        ->not->toContain('$_GET')
        ->not->toContain('$_POST')
        ->not->toMatch('/\bLIMIT\s+[:?0-9]/i')
        ->not->toMatch('/\bOFFSET\s+[:?0-9]/i');
});
