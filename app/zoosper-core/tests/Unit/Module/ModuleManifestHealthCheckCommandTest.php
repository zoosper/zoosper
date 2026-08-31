<?php

declare(strict_types=1);

it('publishes a strict manifest health check with stable exit semantics', function (): void {
    $source = file_get_contents(dirname(__DIR__, 5) . '/app/zoosper-core/src/Console/BuiltIn/ManifestCheckCommand.php');

    expect($source)
        ->toContain("public function name(): string { return 'module:manifest:check'; }")
        ->toContain("\$healthy = \$status['status'] === 'fresh'")
        ->toContain('if ($healthy)')
        ->toContain('Module manifest check passed: fresh')
        ->toContain('Module manifest check failed:')
        ->toContain('bin/zoosper compile');
});

it('verifies the compiled manifest before deploy reports completion', function (): void {
    $source = file_get_contents(dirname(__DIR__, 5) . '/app/zoosper-core/src/Console/BuiltIn/DeployCommand.php');
    $verification = strpos($source, 'Module manifest post-compile verification passed.');
    $completion = strpos($source, '== Deploy complete ==');

    expect($verification)->not->toBeFalse()
        ->and($completion)->not->toBeFalse()
        ->and($verification)->toBeLessThan($completion)
        ->and($source)->toContain('Compiled module manifest failed post-compile verification:');
});










