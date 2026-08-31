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
        $r=is_array($cache['redis']??null)?$cache['redis']:[];
        $connection=new RedisConnection(host:(string)($r['host']??'127.0.0.1'),port:(int)($r['port']??6379),password:isset($r['password'])&&$r['password']!==''&&$r['password']!==null?(string)$r['password']:null,database:(int)($r['database']??0),prefix:(string)($r['prefix']??'zoosper:cache:'));
        $e=$this->section('encryption');
        $encryption=new EncryptionConfig(new ObjectConfigAdapter(new ArrayConfig(['encryption'=>['key'=>(string)($e['key']??''),'cipher'=>(string)($e['cipher']??'aes-256-gcm')]])));
        return new RedisCacheDriver($connection,$cacheConfig,new CacheValueSigner($encryption));
    }
    private function section(string $key): array { $value=$this->config->get($key,[]); return is_array($value)?$value:[]; }
    private function resolvePath(string $path): string { if(str_starts_with($path,'/')) return rtrim($path,'/'); $path=trim($path,'/'); return rtrim($this->basePath,'/').'/'.(str_starts_with($path,'var/')?$path:'var/'.$path); }
}
final readonly class ArrayConfig { public function __construct(private array $values) {} public function get(string $key,mixed $default=null):mixed { $v=$this->values; foreach(explode('.',$key) as $part){if(!is_array($v)||!array_key_exists($part,$v)) return $default;$v=$v[$part];} return $v; } }











