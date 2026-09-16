<?php
declare(strict_types=1);
namespace Zoosper\Cache\Factory;
use Marko\Cache\Config\CacheConfig;
use Marko\Cache\Contracts\CacheInterface as MarkoCacheInterface;
use Marko\Cache\File\Driver\FileCacheDriver;
use Marko\Cache\Redis\Driver\RedisCacheDriver;
use Marko\Cache\Redis\RedisConnection;
use Marko\Cache\Redis\Signer\CacheValueSigner;
use Marko\Encryption\Config\EncryptionConfig;
use RuntimeException;
use Zoosper\Cache\Config\ObjectConfigAdapter;
use Zoosper\Cache\Contract\CacheInterface;
use Zoosper\Cache\Driver\MarkoCacheAdapter;
final readonly class CacheDriverFactory
{
    public function __construct(private object $config, private string $basePath) {}
    public function create(): CacheInterface
    {
        $cache=$this->section('cache'); $driver=strtolower(trim((string)($cache['driver']??'file')));
        $path=$this->resolvePath((string)($cache['path']??'var/cache/page'));
        $cacheConfig=new CacheConfig(new ObjectConfigAdapter(new ArrayConfig(['cache'=>['driver'=>$driver,'path'=>$path,'default_ttl'=>(int)($cache['default_ttl']??3600)]])));
        $marko=match($driver){'file'=>new FileCacheDriver($cacheConfig),'redis'=>$this->redis($cache,$cacheConfig),default=>throw new RuntimeException('Unsupported cache driver: "'.$driver.'". Supported drivers: file, redis.')};
        return new MarkoCacheAdapter($marko);
    }
    private function redis(array $cache, CacheConfig $cacheConfig): MarkoCacheInterface
    {
        $redis = is_array($cache['redis'] ?? null) ? $cache['redis'] : [];
        $encryption = $this->section('encryption');
        $signingKey = trim((string) ($encryption['key'] ?? ''));

        if (strlen($signingKey) < 32 || $this->isInsecureSecret($signingKey)) {
            throw new RuntimeException(
                'Redis cache requires a strong CACHE_ENCRYPTION_KEY for signed cache values.',
            );
        }

        $connection = new RedisConnection(
            host: (string) ($redis['host'] ?? '127.0.0.1'),
            port: (int) ($redis['port'] ?? 6379),
            password: isset($redis['password']) && trim((string) $redis['password']) !== ''
                ? (string) $redis['password']
                : null,
            database: (int) ($redis['database'] ?? 0),
            prefix: (string) ($redis['prefix'] ?? 'zoosper:cache:'),
        );

        $encryptionConfig = new EncryptionConfig(
            new ObjectConfigAdapter(
                new ArrayConfig([
                    'encryption' => [
                        'key' => $signingKey,
                        'cipher' => (string) ($encryption['cipher'] ?? 'aes-256-gcm'),
                    ],
                ]),
            ),
        );

        return new RedisCacheDriver(
            $connection,
            $cacheConfig,
            new CacheValueSigner($encryptionConfig),
        );
    }

    private function isInsecureSecret(string $value): bool
    {
        if ($value === '') {
            return true;
        }

        return in_array(
            strtolower($value),
            [
                'change-me',
                'change-me-before-production',
                'secret',
                'changeme',
                'placeholder',
                'default',
                'password',
                'test',
                'null',
            ],
            true,
        );
    }
    private function section(string $key): array { $value=$this->config->get($key,[]); return is_array($value)?$value:[]; }
    private function resolvePath(string $path): string { if(str_starts_with($path,'/')) return rtrim($path,'/'); $path=trim($path,'/'); return rtrim($this->basePath,'/').'/'.(str_starts_with($path,'var/')?$path:'var/'.$path); }
}
final readonly class ArrayConfig { public function __construct(private array $values) {} public function get(string $key,mixed $default=null):mixed { $v=$this->values; foreach(explode('.',$key) as $part){if(!is_array($v)||!array_key_exists($part,$v)) return $default;$v=$v[$part];} return $v; } }











