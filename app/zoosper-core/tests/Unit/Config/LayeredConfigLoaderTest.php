<?php

declare(strict_types=1);

use Zoosper\Core\Config\LayeredConfigLoader;

it('merges ordered layers and preserves source normalization', function (): void {
    $result = (new LayeredConfigLoader())->load([
        ['source' => 0, 'config' => ['nested' => ['first' => 1, 'second' => 2], 'list' => [1, 2]]],
        ['config' => ['nested' => ['second' => 3], 'list' => [4]]],
        ['source' => null, 'config' => null],
    ]);

    expect($result->sources)->toBe(['0', 'unknown', 'unknown'])
        ->and($result->config)->toBe(['nested' => ['first' => 1, 'second' => 3], 'list' => [4]]);
});

it('rejects a non-array configuration with its normalized source', function (): void {
    expect(fn () => (new LayeredConfigLoader())->load([
        ['source' => 42, 'config' => 'invalid'],
    ]))->toThrow(InvalidArgumentException::class, 'Layered config layer must contain an array config for source: 42');
});