<?php

declare(strict_types=1);

namespace Zoosper\AdminGrid\Tests\Unit;

test('page-size enhancement resets pagination and submits the existing GET form', function (): void {
    $script = (string) file_get_contents(
        dirname(__DIR__, 2) . '/resources/admin/js/grid-workspace-page-size.js',
    );

    expect($script)->toContain('input[name="page"]')
        ->toContain("page.value = '1'")
        ->toContain('form.requestSubmit()')
        ->not->toContain('fetch(')
        ->not->toContain('innerHTML');
});
