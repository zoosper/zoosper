<?php
declare(strict_types=1);
namespace Zoosper\Config\Bridge;
use Marko\Config\ConfigRepositoryInterface;
use Marko\Config\Exceptions\ConfigException;
use Marko\Config\Exceptions\ConfigNotFoundException;
use stdClass;
final class MarkoConfigRepositoryAdapter implements ConfigRepositoryInterface
{
    private readonly stdClass $missing;
    public function __construct(private readonly object $config)
    {
        if (!method_exists($config, 'get')) {
            throw new ConfigException('The Zoosper configuration bridge requires a get() method.');
        }
        $this->missing = new stdClass();
    }
    public function get(string $key, ?string $scope = null): mixed
    {
        $value = $this->config->get($key, $this->missing);
        if ($value === $this->missing) throw new ConfigNotFoundException($key);
        return $value;
    }
    public function has(string $key, ?string $scope = null): bool { return $this->config->get($key, $this->missing) !== $this->missing; }
    public function getString(string $key, ?string $scope = null): string { $v=$this->get($key,$scope); if(!is_scalar($v)) throw $this->typeError($key,'string',$v); return (string)$v; }
    public function getInt(string $key, ?string $scope = null): int { $v=$this->get($key,$scope); if(!is_numeric($v)) throw $this->typeError($key,'integer',$v); return (int)$v; }
    public function getBool(string $key, ?string $scope = null): bool { $v=$this->get($key,$scope); if(!is_scalar($v)) throw $this->typeError($key,'boolean',$v); return (bool)$v; }
    public function getFloat(string $key, ?string $scope = null): float { $v=$this->get($key,$scope); if(!is_numeric($v)) throw $this->typeError($key,'float',$v); return (float)$v; }
    public function getArray(string $key, ?string $scope = null): array { $v=$this->get($key,$scope); if(!is_array($v)) throw $this->typeError($key,'array',$v); return $v; }
    public function all(?string $scope = null): array { throw new ConfigException('MarkoConfigRepositoryAdapter does not support all().', "Zoosper's application repository does not expose its full merged array.", 'Access specific keys instead.'); }
    public function withScope(string $scope): ConfigRepositoryInterface { throw new ConfigException('MarkoConfigRepositoryAdapter does not support withScope().', 'Application and persisted scoped configuration remain separate boundaries.', 'Use the scoped configuration repository explicitly.'); }
    private function typeError(string $key,string $expected,mixed $value): ConfigException { return new ConfigException(sprintf('Configuration key "%s" is not a %s',$key,$expected),sprintf('Expected %s, got %s',$expected,get_debug_type($value)),'Correct the module or project configuration value.'); }
}
