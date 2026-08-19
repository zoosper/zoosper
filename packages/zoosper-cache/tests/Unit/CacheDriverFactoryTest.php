<?php

declare(strict_types=1);

use Marko\Cache\File\Driver\FileCacheDriver;
use Marko\Cache\Redis\Driver\RedisCacheDriver;
use Zoosper\Cache\Contract\CacheInterface as ZoosperCacheInterface;
use Zoosper\Cache\Driver\MarkoCacheAdapter;
use Zoosper\Cache\Factory\CacheDriverFactory;
use Zoosper\Core\Config\ConfigRepository;

function cacheDriverFactoryTestInstance(array $cacheOverrides = [], array $encryptionOverrides = []): CacheDriverFactory
{
    $config = ConfigRepository::fromArray([
        'cache' => array_replace([
            'driver' => 'file',
            'path' => 'var/cache/zoosper-cache-factory-test-' . bin2hex(random_bytes(4)),
            'default_ttl' => 3600,
            'redis' => ['host' => '127.0.0.1', 'port' => 6379, 'password' => null, 'database' => 0, 'prefix' => 'zoosper-test:'],
        ], $cacheOverrides),
        'encryption' => array_replace(['key' => '', 'cipher' => 'aes-256-gcm'], $encryptionOverrides),
    ]);
    return new CacheDriverFactory($config, dirname(__DIR__, 4));
}

it('builds a genuinely working FileCacheDriver, proven with a real set/get roundtrip', function (): void {
    $driver = cacheDriverFactoryTestInstance()->create();
    expect($driver)->toBeInstanceOf(ZoosperCacheInterface::class)
        ->and($driver)->toBeInstanceOf(MarkoCacheAdapter::class)
        ->and($driver->markoDriver())->toBeInstanceOf(FileCacheDriver::class);
    $key = 'zoosper-cache-factory-test-key-' . bin2hex(random_bytes(4));
    expect($driver->has($key))->toBeFalse();
    $driver->set($key, ['hello' => 'world'], 60);
    expect($driver->has($key))->toBeTrue();
    expect($driver->get($key))->toBe(['hello' => 'world']);
    $driver->delete($key);
    expect($driver->has($key))->toBeFalse();
});

it('defaults to the file driver when cache.driver is not explicitly set', function (): void {
    $config = ConfigRepository::fromArray(['cache' => ['path' => 'var/cache/zoosper-cache-factory-default-test-' . bin2hex(random_bytes(4))]]);
    $driver=(new CacheDriverFactory($config, dirname(__DIR__, 4)))->create();
    expect($driver)->toBeInstanceOf(MarkoCacheAdapter::class)
        ->and($driver->markoDriver())->toBeInstanceOf(FileCacheDriver::class);
});

it('throws a clear error for an unsupported driver name', function (): void {
    expect(fn () => cacheDriverFactoryTestInstance(['driver' => 'not-a-real-driver'])->create())
        ->toThrow(RuntimeException::class, 'Unsupported cache driver: "not-a-real-driver"');
});

it('constructs a RedisCacheDriver object graph correctly WITHOUT requiring a real Redis connection', function (): void {
    $driver = cacheDriverFactoryTestInstance(['driver' => 'redis'], ['key' => 'test-signing-key-value'])->create();
    expect($driver)->toBeInstanceOf(MarkoCacheAdapter::class)
        ->and($driver->markoDriver())->toBeInstanceOf(RedisCacheDriver::class);
});

it('performs a REAL Redis set/get roundtrip when Redis is actually reachable (explicitly skipped otherwise)', function (): void {
    $driver = cacheDriverFactoryTestInstance(['driver' => 'redis'], ['key' => 'test-signing-key-for-real-redis-roundtrip'])->create();
    $key = 'zoosper-cache-factory-real-redis-test-' . bin2hex(random_bytes(4));
    try {
        $driver->set($key, 'a-real-value', 30);
        $value = $driver->get($key);
    } catch (\Throwable $exception) {
        $this->markTestSkipped('Could not reach a real Redis server from this test environment (' . $exception->getMessage() . ').');
        return;
    }
    expect($value)->toBe('a-real-value');
    $driver->delete($key);
});
