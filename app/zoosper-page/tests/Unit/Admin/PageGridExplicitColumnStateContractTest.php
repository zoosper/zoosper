<?php

declare(strict_types=1);

namespace Zoosper\Page\Tests\Unit\Admin;

test('Page workspace gives explicit visible column query state precedence', function (): void {
    $root = dirname(__DIR__, 5);
    $source = (string) file_get_contents(
        $root . '/app/zoosper-page/src/Admin/PageGridWorkspace.php',
    );

    expect($source)->toContain("array_key_exists('visible_columns', \$queryState)")
        ->toContain('withVisibleColumnKeys($visible)')
        ->toContain('!$column->toggleable')
        ->toContain('GridColumnOrderer');
});










