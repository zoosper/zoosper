<?php

declare(strict_types=1);

it('wires Permission Explorer assets from the rendered permission partial', function (): void {
    $view = file_get_contents(dirname(__DIR__, 3) . '/zoosper-admin/resources/views/admin/roles/permission-tree.php');
    expect($view)
        ->toContain('/asset/zoosper-auth/css/permission-explorer.css')
        ->toContain('/asset/zoosper-auth/js/permission-explorer.js');
});

it('discovers the existing role form without requiring new server markup', function (): void {
    $runtime = file_get_contents(dirname(__DIR__, 2) . '/resources/assets/admin/js/permission-explorer.js');
    expect($runtime)->toContain("checkbox.closest('form')")->toContain('permission_ids[]');
});
