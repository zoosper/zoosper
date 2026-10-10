<?php

declare(strict_types=1);

it('locks the second bounded Psalm correctness cohort', function (): void {
    $root = dirname(__DIR__, 5);

    $dispatcher = (string) file_get_contents($root . '/app/zoosper-core/src/Event/EventDispatcher.php');
    expect($dispatcher)
        ->toContain('is_string($callableTarget) ? $callableTarget')
        ->toContain('is_string($method) ? $method')
        ->not->toContain('(string) ( ?? \unknown\)');

    $siteDomains = (string) file_get_contents($root . '/app/zoosper-site/src/Admin/Grid/SiteDomainGrid.php');
    expect($siteDomains)->toContain('static fn(mixed $value): string');

    $base32 = (string) file_get_contents($root . '/app/zoosper-two-factor/src/Totp/Base32.php');
    expect($base32)->toContain('chr((int) bindec($chunk))');

    $orders = (string) file_get_contents($root . '/packages/zoosper-store-orders/src/Api/StoreOrderRowMapper.php');
    expect($orders)->toContain('@return array<string, mixed>');
});
