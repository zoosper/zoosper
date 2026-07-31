<?php

declare(strict_types=1);

namespace Zoosper\Core\Cache;

use Marko\Cache\Config\CacheConfig;
use Marko\Cache\Contracts\CacheInterface;
use Marko\Cache\File\Driver\FileCacheDriver;
use Marko\Cache\Redis\Driver\RedisCacheDriver;
use Marko\Cache\Redis\RedisConnection;
use Marko\Cache\Redis\Signer\CacheValueSigner;
use Marko\Encryption\Config\EncryptionConfig;
use RuntimeException;
use Zoosper\Core\Config\ConfigRepository;
use Zoosper\Core\Config\MarkoConfigRepositoryAdapter;
use Zoosper\Core\Filesystem\ProjectPathResolver;

final readonly class CacheDriverFactory
{
    public function __construct(private ConfigRepository $config, private ProjectPathResolver $paths)
    {
    }

    public function create(): CacheInterface
    {
        $cacheSection = $this->config->array('cache');
        $driver = strtolower(trim((string) ($cacheSection['driver'] ?? 'file')));
        $resolvedPath = $this->resolveCachePath((string) ($cacheSection['path'] ?? 'var/cache/page'));

        $cacheConfigRepository = ConfigRepository::fromArray([
            'cache' => [
                'driver' => $driver,
                'path' => $resolvedPath,
                'default_ttl' => (int) ($cacheSection['default_ttl'] ?? 3600),
            ],
        ]);
        $cacheConfig = new CacheConfig(new MarkoConfigRepositoryAdapter($cacheConfigRepository));

        return match ($driver) {
            'file' => new FileCacheDriver($cacheConfig),
            'redis' => $this->createRedisDriver($cacheSection, $cacheConfig),
            default => throw new RuntimeException('Unsupported cache driver: "' . $driver . '". Supported drivers: file, redis.'),
        };
    }

    private function createRedisDriver(array $cacheSection, CacheConfig $cacheConfig): RedisCacheDriver
    {
        $redisSection = is_array($cacheSection['redis'] ?? null) ? $cacheSection['redis'] : [];
        $connection = new RedisConnection(
            host: (string) ($redisSection['host'] ?? '127.0.0.1'),
            port: (int) ($redisSection['port'] ?? 6379),
            password: isset($redisSection['password']) && $redisSection['password'] !== '' && $redisSection['password'] !== null ? (string) $redisSection['password'] : null,
            database: (int) ($redisSection['database'] ?? 0),
            prefix: (string) ($redisSection['prefix'] ?? 'zoosper:cache:'),
        );

        $encryptionSection = $this->config->array('encryption');
        $encryptionConfigRepository = ConfigRepository::fromArray([
            'encryption' => [
                'key' => (string) ($encryptionSection['key'] ?? ''),
                'cipher' => (string) ($encryptionSection['cipher'] ?? 'aes-256-gcm'),
            ],
        ]);
        $encryptionConfig = new EncryptionConfig(new MarkoConfigRepositoryAdapter($encryptionConfigRepository));
        return new RedisCacheDriver($connection, $cacheConfig, new CacheValueSigner($encryptionConfig));
    }

    private function resolveCachePath(string $path): string
    {
        if (str_starts_with($path, '/')) {
            return rtrim($path, '/');
        }
        $path = trim($path, '/');
        return str_starts_with($path, 'var/') ? $this->paths->varPath(substr($path, 4)) : $this->paths->varPath($path);
    }
}
