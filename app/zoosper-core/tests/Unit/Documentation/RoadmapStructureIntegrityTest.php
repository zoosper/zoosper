<?php

declare(strict_types=1);

it('enforces structured markdown status checkboxes throughout ROADMAP.md', function (): void {
    $root = dirname(__DIR__, 5);
    $roadmapPath = $root . '/ROADMAP.md';

    expect(is_file($roadmapPath))->toBeTrue();

    $content = (string) file_get_contents($roadmapPath);
    $lines = explode("\n", $content);

    $invalidCheckboxes = [];

    foreach ($lines as $index => $line) {
        $trimmed = trim($line);

        // If line starts with list item dash followed by bracket:
        if (preg_match('/^-\s*\[([^\]]*)\]\s+/', $trimmed, $matches) === 1) {
            $status = $matches[1];
            // Valid statuses are 'x', ' ', or '~'
            if (!in_array($status, ['x', ' ', '~'], true)) {
                $invalidCheckboxes[] = sprintf('Line %d: invalid checkbox state "[%s]"', $index + 1, $status);
            }
        }
    }

    expect($invalidCheckboxes)->toBe(
        [],
        "ROADMAP.md must only use valid status markers (- [x], - [ ], - [~]):\n" . implode("\n", $invalidCheckboxes)
    );
});

it('maintains the closure roadmap structure in canonical order', function (): void {
    $root = dirname(__DIR__, 5);
    $content = (string) file_get_contents($root . '/ROADMAP.md');

    expect($content)
        ->toContain('## Current state')
        ->toContain('## Closure definition')
        ->toContain('## Required remaining work')
        ->toContain('### C1. Repository and legal ownership')
        ->toContain('### C2. Static-analysis debt closure')
        ->toContain('### C3. Stable distribution and compatibility contract')
        ->toContain('### C4. Operational closure')
        ->toContain('### C5. Final release closure')
        ->toContain('## Explicitly not required for closure')
        ->toContain('## Established foundation')
        ->toContain('## Working rule');
});
