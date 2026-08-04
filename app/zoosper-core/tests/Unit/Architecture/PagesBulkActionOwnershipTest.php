<?php

declare(strict_types=1);

it('keeps Pages as a declarative bulk-action consumer', function (): void {
    $root = dirname(__DIR__, 5);
    $declaration = file_get_contents($root . '/app/zoosper-page/src/Admin/PageGridBulkActions.php');
    $controller = file_get_contents($root . '/app/zoosper-page/src/Admin/Controller/PageAdminController.php');
    expect($declaration)->not->toBeFalse()
        ->and($declaration)->toContain("id: 'export.selected'")
        ->and($declaration)->not->toContain('querySelector')
        ->and($controller)->toContain('GridBulkActionManifestRenderer')
        ->and($controller)->not->toContain('new GridBulkActionDefinition');
});
