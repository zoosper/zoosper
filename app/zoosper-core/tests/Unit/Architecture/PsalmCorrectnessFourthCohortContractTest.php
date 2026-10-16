<?php

declare(strict_types=1);

it('locks strict module discovery and typed sitemap aggregation', function (): void {
    $root = dirname(__DIR__, 5);
    $config = (string) file_get_contents($root . '/app/zoosper-core/src/Config/ModuleConfigAggregator.php');
    $sitemap = (string) file_get_contents($root . '/app/zoosper-seo/src/Sitemap/SitemapAggregator.php');
    expect($config)->toContain('elseif (is_array($settings))')->toContain('array_filter($settings, is_string(...))')->toContain('malformed shapes contribute nothing')
        ->and($sitemap)->toContain('/** @psalm-api */')->not->toContain('$entry instanceof SitemapEntry');
});
