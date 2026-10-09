<?php

declare(strict_types=1);

it('publishes the mandatory release-to-master promotion process', function (): void {
    $root = dirname(__DIR__, 5);
    $path = $root . '/docs/releases/release-promotion.md';
    $runbook = (string) file_get_contents($path);
    $builder = (string) file_get_contents($root . '/docs-site/build.php');

    expect($path)->toBeFile()
        ->and($runbook)->toContain('# Release promotion to the default branch')
        ->toContain('mandatory after a Zoosper release tag')
        ->toContain('Fast-forward remote `master`')
        ->toContain('No force push.')
        ->toContain('No retagging or tag deletion.')
        ->toContain('No merge of post-release `dev` commits into `master`.')
        ->toContain('Check the public default-branch README')
        ->and($builder)->toContain("'releases/release-promotion'")
        ->toContain("'releases/release-promotion' => 'Release Promotion'");
});

it('builds the release-promotion page into the canonical website', function (): void {
    $root = dirname(__DIR__, 5);
    $build = $root . '/docs-site/build';
    $command = escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($root . '/docs-site/build.php');
    exec($command . ' 2>&1', $output, $code);

    expect($code)->toBe(0, implode("\n", $output))
        ->and($build . '/releases/release-promotion/index.html')->toBeFile()
        ->and((string) file_get_contents($build . '/releases/release-promotion/index.html'))
        ->toContain('Release promotion to the default branch')
        ->toContain('Release Promotion');
});
