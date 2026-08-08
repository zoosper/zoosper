<?php

declare(strict_types=1);

it('validates manifest output format once before command dispatch', function (): void {
    $source = file_get_contents(dirname(__DIR__, 5) . '/bin/zoosper');

    expect($source)
        ->toContain('in_array($command, [\'module:manifest:status\', \'module:manifest:check\'], true)')
        ->toContain('$format = $options[\'format\'] ?? \'text\';')
        ->toContain('if (!in_array($format, [\'text\', \'json\'], true))')
        ->toContain('Unsupported format \'{$format}\'. Expected text or json.')
        ->toContain('exit(2);');
});

it('keeps explicit JSON branches in both manifest command objects', function (): void {
    $root = dirname(__DIR__, 5);
    $status = file_get_contents($root . '/app/zoosper-core/src/Console/BuiltIn/ManifestStatusCommand.php');
    $check = file_get_contents($root . '/app/zoosper-core/src/Console/BuiltIn/ManifestCheckCommand.php');

    expect($status)->toContain("if (\$format === 'json')")
        ->and($check)->toContain("if (\$format === 'json')");
});
