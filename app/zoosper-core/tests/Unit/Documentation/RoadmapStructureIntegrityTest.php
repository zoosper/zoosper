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
        if (preg_match('/^-\s*\[([^\]]*)\]/', $trimmed, $matches) === 1) {
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

it('maintains expected major roadmap section headings in canonical order', function (): void {
    $root = dirname(__DIR__, 5);
    $content = (string) file_get_contents($root . '/ROADMAP.md');

    expect($content)
        ->toContain('## 1. Core Platform & Architecture')
        ->toContain('## 2. Sites, Pages & Content')
        ->toContain('## 3. Themes & Templating')
        ->toContain('## 4. Admin & Auth')
        ->toContain('## 5. Security')
        ->toContain('## 6. Media')
        ->toContain('## 7. Mail')
        ->toContain('## 8. API')
        ->toContain('## 9. Modular Asset Pipeline')
        ->toContain('## 10. Caching & Performance')
        ->toContain('## 11. Quality, Tooling & Repo Hygiene');
});










