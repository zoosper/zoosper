<?php

declare(strict_types=1);

namespace Zoosper\Core\Tests\Unit\Scaffold;

use Zoosper\Errors\ZoosperException;
use Zoosper\Core\Scaffold\ModuleName;

test('normalises a valid module name', function () {
    $name = ModuleName::fromInput('Acme_Blog');

    expect($name->raw)->toBe('Acme_Blog');
    expect($name->namespace)->toBe('Acme\Blog');
    expect($name->folderName)->toBe('acme-blog');
});

test('rejects invalid module names with a descriptive exception', function () {
    expect(fn () => ModuleName::fromInput('bad-name'))->toThrow(ZoosperException::class);
});

test('preserves module name normalization at input boundaries', function (string $input, string $raw, string $namespace, string $folder): void {
    $name = ModuleName::fromInput($input);

    expect($name->raw)->toBe($raw)
        ->and($name->namespace)->toBe($namespace)
        ->and($name->folderName)->toBe($folder);
})->with([
    ['  ACME_MOVIELibrary  ', 'Acme_Movielibrary', 'Acme\\Movielibrary', 'acme-movielibrary'],
    ['a1_B2', 'A1_B2', 'A1\\B2', 'a1-b2'],
]);

test('rejects malformed module name segments', function (string $input): void {
    expect(fn () => ModuleName::fromInput($input))->toThrow(ZoosperException::class);
})->with(['', 'Acme', '_Blog', 'Acme_', 'Acme_Blog_Extra', '1Acme_Blog', 'Acme_B-log', 'Äcme_Blog']);










