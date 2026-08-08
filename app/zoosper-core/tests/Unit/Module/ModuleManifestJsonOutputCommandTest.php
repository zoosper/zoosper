<?php

declare(strict_types=1);

it('keeps text output as the default and adds explicit JSON status output', function (): void {
    $source = file_get_contents(dirname(__DIR__, 5) . '/app/zoosper-core/src/Console/BuiltIn/ManifestStatusCommand.php');

    expect($source)
        ->toContain("\$format = \$options['format'] ?? 'text'")
        ->toContain("if (\$format === 'json')")
        ->toContain('JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES')
        ->toContain('Module manifest status:');
});

it('adds a healthy boolean while preserving strict check exit codes in JSON mode', function (): void {
    $source = file_get_contents(dirname(__DIR__, 5) . '/app/zoosper-core/src/Console/BuiltIn/ManifestCheckCommand.php');

    expect($source)
        ->toContain("['healthy' => \$healthy] + \$status")
        ->toContain('return $healthy ? 0 : 1;')
        ->toContain('Module manifest check passed: fresh');
});
