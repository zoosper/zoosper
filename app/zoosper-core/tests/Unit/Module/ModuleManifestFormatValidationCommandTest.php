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

it('reuses the validated format for both JSON branches', function (): void {
    $source = file_get_contents(dirname(__DIR__, 5) . '/bin/zoosper');

    expect(substr_count($source, 'if ($format === \'json\')'))->toBe(2);
});
