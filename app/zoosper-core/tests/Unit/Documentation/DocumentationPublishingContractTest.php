<?php

declare(strict_types=1);

it('publishes canonical documentation to the dedicated website repository', function (): void {
    $root = dirname(__DIR__, 5);
    $workflow = (string) file_get_contents($root . '/.github/workflows/docs-site.yml');
    $readme = (string) file_get_contents($root . '/docs-site/README.md');

    expect(trim((string) file_get_contents($root . '/docs-site/CNAME')))->toBe('docs.zoosper.com')
        ->and($workflow)->toContain('branches: [dev]')
        ->toContain('contents: read')
        ->toContain('repository: zoosper/zoosper-cms-website')
        ->toContain('ref: master')
        ->toContain('secrets.DOCS_WEBSITE_TOKEN')
        ->toContain("rsync -a --delete --exclude='.git/'")
        ->toContain('git push origin HEAD:master')
        ->not->toContain('pages: write')
        ->not->toContain('id-token: write')
        ->not->toContain('actions/configure-pages')
        ->not->toContain('actions/upload-pages-artifact')
        ->not->toContain('actions/deploy-pages')
        ->and($readme)->toContain('zoosper/zoosper-cms-website')
        ->toContain('DOCS_WEBSITE_TOKEN');
});

it('builds the dedicated website repository payload with custom-domain metadata', function (): void {
    $root = dirname(__DIR__, 5);
    $build = $root . '/docs-site/build';

    expect(trim((string) file_get_contents($build . '/CNAME')))->toBe('docs.zoosper.com')
        ->and($build . '/.nojekyll')->toBeFile()
        ->and($build . '/index.html')->toBeFile();
});
