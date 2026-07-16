<?php

declare(strict_types=1);

namespace Zoosper\Core\Tests\Unit\Config;

use Zoosper\Core\Config\ConfigRepository;

test('fromArray exposes values via dot notation', function () {
    $config = ConfigRepository::fromArray([
        'logging' => ['default_file' => 'system.log'],
    ]);

    expect($config->get('logging.default_file'))->toBe('system.log');
});

test('get returns the default for a missing key', function () {
    $config = ConfigRepository::fromArray([]);

    expect($config->get('missing.key', 'fallback'))->toBe('fallback');
});

test('array returns an empty array for a missing key', function () {
    $config = ConfigRepository::fromArray([]);

    expect($config->array('missing'))->toBe([]);
});