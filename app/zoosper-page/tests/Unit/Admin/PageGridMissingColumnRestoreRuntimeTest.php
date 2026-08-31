<?php

declare(strict_types=1);

namespace Zoosper\Page\Tests\Unit\Admin;

test('restoring a server-hidden column submits the canonical Grid form', function (): void {
    $root = dirname(__DIR__, 5);
    $script = (string) file_get_contents(
        $root . '/app/zoosper-admin/resources/assets/js/zoosper-grid-columns.js',
    );

    expect($script)
        ->toContain('BEGIN GRID MISSING COLUMN RESTORE')
        ->toContain('if (!checkbox.checked || !form || !table)')
        ->toContain('table.querySelector(selector) === null')
        ->toContain("page.value = '1'")
        ->toContain('form.requestSubmit()');
});










