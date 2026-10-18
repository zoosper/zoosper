<?php

declare(strict_types=1);

it('authorises URL Rewrite update before object lookup', function (): void {
    $source = preg_replace('/\s+/', '', (string) file_get_contents(
        dirname(__DIR__, 5) . '/app/zoosper-url-rewrite/src/Api/UrlRewriteApiController.php',
    ));
    $start = strpos($source, 'publicfunctionupdate(');
    $end = strpos($source, 'publicfunctiondisable(');
    expect($start)->not->toBeFalse()->and($end)->not->toBeFalse();
    $method = substr($source, (int) $start, (int) $end - (int) $start);
    $authorise = strpos($method, '$this->principals->require($r)');
    $lookup = strpos($method, '$this->rewrite($r)');
    expect($authorise)->not->toBeFalse()
        ->and($lookup)->not->toBeFalse()
        ->and($authorise)->toBeLessThan($lookup);
});
