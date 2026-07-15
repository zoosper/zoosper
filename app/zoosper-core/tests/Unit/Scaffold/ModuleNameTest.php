<?php

declare(strict_types=1);

namespace Zoosper\Core\Tests\Unit\Scaffold;

use Zoosper\Core\Exception\ZoosperException;
use Zoosper\Core\Scaffold\ModuleName;

test('normalises a valid module name', function () {
    $name = ModuleName::fromInput('Acme_Blog');

    expect($name->raw)->toBe('Acme_Blog');
    expect($name->namespace)->toBe('Acme\\Blog');
    expect($name->folderName)->toBe('acme-blog');
});

test('rejects invalid module names with a descriptive exception', function () {
    expect(fn () => ModuleName::fromInput('bad-name'))->toThrow(ZoosperException::class);
});
