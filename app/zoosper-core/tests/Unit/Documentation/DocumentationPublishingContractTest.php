<?php

declare(strict_types=1);

it('publishes canonical documentation through the official GitHub Pages pipeline', function (): void {
    $root = dirname(__DIR__, 5);
    $workflow = (string) file_get_contents($root . '/.github/workflows/docs-site.yml');
    $readme = (string) file_get_contents($root . '/docs-site/README.md');

    expect(trim((string) file_get_contents($root . '/docs-site/CNAME')))->toBe('docs.zoosper.com')
        ->and($workflow)->toContain('branches: [dev]')
        ->toContain('pages: write')
        ->toContain('id-token: write')
        ->toContain('actions/configure-pages@v5')
        ->toContain('actions/upload-pages-artifact@v3')
        ->toContain('actions/deploy-pages@v4')
        ->not->toContain('actions/upload-artifact@v4')
        ->and($readme)->toContain('Generated output remains ignored')
        ->toContain('does not read, modify, commit, or push that repository');
});

it('builds deployable custom-domain output without repository metadata', function (): void {
    $root = dirname(__DIR__, 5);
    $build = $root . '/docs-site/build';

    expect(trim((string) file_get_contents($build . '/CNAME')))->toBe('docs.zoosper.com')
        ->and($build . '/.nojekyll')->toBeFile()
        ->and($build . '/index.html')->toBeFile();
});
