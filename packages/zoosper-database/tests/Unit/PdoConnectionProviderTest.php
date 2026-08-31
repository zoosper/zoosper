<?php

declare(strict_types=1);

namespace Zoosper\Database\Tests;

use PDO;
use Zoosper\Database\PdoConnectionProvider;

test('PDO connection is created lazily and memoized', function (): void {
    $calls = 0;
    $pdo = new PDO('sqlite::memory:');
    $provider = new PdoConnectionProvider(static function () use (&$calls, $pdo): PDO {
        ++$calls;

        return $pdo;
    });

    expect($calls)->toBe(0);
    expect($provider->get())->toBe($pdo);
    expect($provider->get())->toBe($pdo);
    expect($calls)->toBe(1);
});











