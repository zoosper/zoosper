<?php
declare(strict_types=1);
namespace Zoosper\Cache\Config;
use Marko\Config\ConfigRepositoryInterface;
use Marko\Config\Exceptions\ConfigException;
use Marko\Config\Exceptions\ConfigNotFoundException;
final readonly class ObjectConfigAdapter implements ConfigRepositoryInterface
{
    public function __construct(private object $config) {}
    public function get(string $key, mixed $default = null, ?string $scope = null): mixed { $value=$this->config->get($key,$default); if ($value===null && func_num_args()<2) throw new ConfigNotFoundException($key); return $value; }
    public function has(string $key, ?string $scope = null): bool { return $this->config->get($key,null)!==null; }
    public function getString(string $key, ?string $scope = null): string { $v=$this->get($key,null,$scope); if(!is_string($v)) throw new ConfigException('Configuration value is not a string.'); return $v; }
    public function getInt(string $key, ?string $scope = null): int { $v=$this->get($key,null,$scope); if(!is_int($v)) throw new ConfigException('Configuration value is not an integer.'); return $v; }
    public function getBool(string $key, ?string $scope = null): bool { $v=$this->get($key,null,$scope); if(!is_bool($v)) throw new ConfigException('Configuration value is not a boolean.'); return $v; }
    public function getFloat(string $key, ?string $scope = null): float { $v=$this->get($key,null,$scope); if(!is_float($v)&&!is_int($v)) throw new ConfigException('Configuration value is not a float.'); return (float)$v; }
    public function getArray(string $key, ?string $scope = null): array { $v=$this->get($key,null,$scope); if(!is_array($v)) throw new ConfigException('Configuration value is not an array.'); return $v; }
    public function all(?string $scope = null): array { throw new ConfigException('ObjectConfigAdapter does not support all().'); }
    public function withScope(string $scope): ConfigRepositoryInterface { throw new ConfigException('ObjectConfigAdapter does not support withScope().'); }
}
