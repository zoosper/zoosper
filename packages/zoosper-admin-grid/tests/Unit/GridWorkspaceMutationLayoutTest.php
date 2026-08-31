<?php

declare(strict_types=1);

namespace Zoosper\AdminGrid\Tests\Unit;

it('ships compact persistence layout without interpolating source assertions', function (): void {
    $root = dirname(__DIR__, 4);
    $renderer = file_get_contents(
        $root . '/packages/zoosper-admin-grid/src/GridWorkspaceMutationFormsRenderer.php',
    );
    $css = file_get_contents(
        $root . '/packages/zoosper-admin-grid/resources/admin/css/grid-workspace.css',
    );

    expect($renderer)->not->toBeFalse()
        ->and($renderer)->toContain('grid-workspace__mutation-form')
        ->and($renderer)->toContain('Saved Grid settings')
        ->and($renderer)->toContain("return \$html . '</section></details>';")
        ->and($renderer)->toContain('id="grid-workspace-settings"')
        ->and($renderer)->toContain('data-grid-settings hidden')
        ->and($css)->not->toBeFalse()
        ->and($css)->toContain('.grid-workspace__mutations');
});











