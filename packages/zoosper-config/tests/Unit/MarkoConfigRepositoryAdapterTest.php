<?php

declare(strict_types=1);

use Marko\Config\Exceptions\ConfigException;
use Marko\Config\Exceptions\ConfigNotFoundException;
use Zoosper\Core\Config\ConfigRepository;
use Zoosper\Config\Bridge\MarkoConfigRepositoryAdapter;

function markoConfigAdapterTestInstance(array $items): MarkoConfigRepositoryAdapter
{
    return new MarkoConfigRepositoryAdapter(ConfigRepository::fromArray($items));
}

it('resolves an existing key via get()', function (): void {
    expect(markoConfigAdapterTestInstance(['cache' => ['driver' => 'file']])->get('cache.driver'))->toBe('file');
});

it('throws ConfigNotFoundException for a genuinely missing key', function (): void {
    expect(fn () => markoConfigAdapterTestInstance(['cache' => ['driver' => 'file']])->get('cache.missing'))
        ->toThrow(ConfigNotFoundException::class);
});

it('has() correctly distinguishes present from missing keys', function (): void {
    $adapter = markoConfigAdapterTestInstance(['cache' => ['driver' => 'file', 'default_ttl' => 0]]);
    expect($adapter->has('cache.driver'))->toBeTrue();
    expect($adapter->has('cache.default_ttl'))->toBeTrue();
    expect($adapter->has('cache.missing'))->toBeFalse();
});

it('getString/getInt/getBool/getFloat/getArray all work correctly for valid values', function (): void {
    $adapter = markoConfigAdapterTestInstance(['x' => ['s' => 'hello', 'i' => '42', 'b' => true, 'f' => '3.14', 'a' => [1, 2, 3]]]);
    expect($adapter->getString('x.s'))->toBe('hello');
    expect($adapter->getInt('x.i'))->toBe(42);
    expect($adapter->getBool('x.b'))->toBeTrue();
    expect($adapter->getFloat('x.f'))->toBe(3.14);
    expect($adapter->getArray('x.a'))->toBe([1, 2, 3]);
});

it('getArray throws ConfigException for a non-array value', function (): void {
    expect(fn () => markoConfigAdapterTestInstance(['x' => ['s' => 'not-an-array']])->getArray('x.s'))
        ->toThrow(ConfigException::class);
});

it('all() and withScope() fail loudly rather than silently returning wrong data', function (): void {
    $adapter = markoConfigAdapterTestInstance(['x' => ['s' => 'hello']]);
    expect(fn () => $adapter->all())->toThrow(ConfigException::class);
    expect(fn () => $adapter->withScope('anything'))->toThrow(ConfigException::class);
});
