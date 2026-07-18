<?php

declare(strict_types=1);

namespace Zoosper\Core\Tests\Unit\Composer;

use Zoosper\Core\Composer\ModulePackageIdentity;

test('derives package identity from vendor underscore module names', function () {
    $identity = ModulePackageIdentity::fromName('Zoosper_Media');

    expect($identity)->not->toBeNull();
    expect($identity->packageName)->toBe('zoosper/media');
    expect($identity->namespace)->toBe('Zoosper\\Media\\');
});

test('derives package identity from historical kebab module names', function () {
    $identity = ModulePackageIdentity::fromName('zoosper-two-factor');

    expect($identity)->not->toBeNull();
    expect($identity->moduleName)->toBe('Zoosper_TwoFactor');
    expect($identity->packageName)->toBe('zoosper/two-factor');
    expect($identity->namespace)->toBe('Zoosper\\TwoFactor\\');
});
