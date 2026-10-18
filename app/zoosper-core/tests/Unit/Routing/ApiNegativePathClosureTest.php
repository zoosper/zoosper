<?php

declare(strict_types=1);

it('keeps protected API adapters on explicit 401 and 403 semantics', function (): void {
    $root = dirname(__DIR__, 5);
    $controllers = [
        'app/zoosper-site/src/Api/SiteApiController.php',
        'app/zoosper-url-rewrite/src/Api/UrlRewriteApiController.php',
        'packages/zoosper-media/src/Api/MediaApiController.php',
    ];

    foreach ($controllers as $relative) {
        $source = preg_replace('/\s+/', '', (string) file_get_contents($root . '/' . $relative));

        expect($source)
            ->toContain("'invalid_bearer_token'")
            ->toContain(',401)')
            ->toContain("'insufficient_scope'")
            ->toContain(',403)');
    }
});

it('authorises URL Rewrite update before object lookup', function (): void {
    $source = preg_replace('/\s+/', '', (string) file_get_contents(
        dirname(__DIR__, 5) . '/app/zoosper-url-rewrite/src/Api/UrlRewriteApiController.php',
    ));
    $start = strpos($source, 'publicfunctionupdate(');
    $end = strpos($source, 'publicfunctiondisable(');

    expect($start)->not->toBeFalse()
        ->and($end)->not->toBeFalse();

    $method = substr($source, $start, $end - $start);
    $authorise = strpos($method, <<<'SOURCE'
principal($r,'url_rewrites:write')
SOURCE);
    $lookup = strpos($method, <<<'SOURCE'
$this->rewrite($r)
SOURCE);

    expect($authorise)->not->toBeFalse()
        ->and($lookup)->not->toBeFalse()
        ->and($authorise)->toBeLessThan($lookup);
});
