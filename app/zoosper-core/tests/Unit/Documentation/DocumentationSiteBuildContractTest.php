<?php

declare(strict_types=1);

it('preserves deployment metadata and uses canonical shared brand assets', function (): void {
    $root = dirname(__DIR__, 5);
    $builder = (string) file_get_contents($root . '/docs-site/build.php');

    expect($builder)
        ->toContain("['.git', 'CNAME']")
        ->toContain('cleanBuildDirectory($outputRoot);')
        ->toContain('app/zoosper-theme/resources/brand/mark.svg')
        ->toContain('public/assets/brand/favicon.svg')
        ->not->toContain('RecursiveDirectoryIterator($repoRoot')
        ->not->toContain('public/assets/zoosper/');

    expect($root . '/app/zoosper-theme/resources/brand/mark.svg')->toBeFile()
        ->and($root . '/public/assets/brand/favicon.svg')->toBeFile();
});










