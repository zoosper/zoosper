<?php

declare(strict_types=1);

it('keeps a compact website-ready canonical documentation set', function (): void {
    $root = dirname(__DIR__, 5);
    $expected = [
        'README.md', 'getting-started.md', 'user-guide.md', 'developer-guide.md',
        'architecture.md', 'modules.md', 'configuration.md', 'cli.md', 'api.md',
        'admin.md', 'themes.md', 'deployment.md', 'upgrade.md',
        'troubleshooting.md', 'testing.md', 'release-checklist.md',
    ];

    $actual = array_map('basename', glob($root . '/docs/*.md') ?: []);
    sort($actual);
    $sortedExpected = $expected;
    sort($sortedExpected);

    expect($actual)->toBe($sortedExpected);
    foreach ($expected as $file) {
        expect($root . '/docs/' . $file)->toBeFile()
            ->and(filesize($root . '/docs/' . $file))->toBeGreaterThan(100);
    }
});

it('documents current truth instead of permanent implementation phases', function (): void {
    $root = dirname(__DIR__, 5);
    $policy = (string) file_get_contents($root . '/docs/README.md');

    expect($policy)
        ->toContain('canonical source')
        ->toContain('Git history and release tags')
        ->toContain('docs.zoosper.com');
});
