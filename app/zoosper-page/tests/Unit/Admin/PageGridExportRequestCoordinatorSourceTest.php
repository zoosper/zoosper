<?php

declare(strict_types=1);

namespace Zoosper\Page\Tests\Unit\Admin;

test('export request reuses one resolved view for criteria and audit', function (): void {
    $source = (string) file_get_contents(
        dirname(__DIR__, 5) . '/app/zoosper-page/src/Admin/PageGridExportRequestCoordinator.php',
    );

    expect($source)->toContain('$state = $resolved[\'state\']')
        ->toContain('$this->rows->exportRows($state->criteria)')
        ->toContain('state: $state')
        ->not->toContain('$_GET')
        ->not->toContain('$_POST');
});
