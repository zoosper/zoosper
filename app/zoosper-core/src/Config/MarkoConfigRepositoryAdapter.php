<?php

declare(strict_types=1);

namespace Zoosper\Core\Config;

use Marko\Config\ConfigRepositoryInterface;
use Marko\Config\Exceptions\ConfigException;
use Marko\Config\Exceptions\ConfigNotFoundException;
use stdClass;

final class MarkoConfigRepositoryAdapter implements ConfigRepositoryInterface
{
    private readonly stdClass $missing;

    public function __construct(private readonly ConfigRepository $config)
    {
        $this->missing = new stdClass();
    }

    public function get(string $key, ?string $scope = null): mixed
    {
        $value = $this->config->get($key, $this->missing);
        if ($value === $this->missing) {
            throw new ConfigNotFoundException($key);
        }
        return $value;
    }

    public function has(string $key, ?string $scope = null): bool
    {
        return $this->config->get($key, $this->missing) !== $this->missing;
    }

    public function getString(string $key, ?string $scope = null): string
    {
        $value = $this->get($key, $scope);
        if (!is_scalar($value)) {
            throw new ConfigException(sprintf('Configuration key "%s" is not a string', $key), sprintf('Expected string, got %s', get_debug_type($value)), 'Ensure your config file returns a string for this key.');
        }
        return (string) $value;
    }

    public function getInt(string $key, ?string $scope = null): int
    {
        $value = $this->get($key, $scope);
        if (!is_numeric($value)) {
            throw new ConfigException(sprintf('Configuration key "%s" is not an integer', $key), sprintf('Expected integer, got %s', get_debug_type($value)), 'Ensure your config file returns an integer for this key.');
        }
        return (int) $value;
    }

    public function getBool(string $key, ?string $scope = null): bool
    {
        $value = $this->get($key, $scope);
        if (!is_scalar($value)) {
            throw new ConfigException(sprintf('Configuration key "%s" is not a boolean', $key), sprintf('Expected boolean, got %s', get_debug_type($value)), 'Ensure your config file returns a boolean for this key.');
        }
        return (bool) $value;
    }

    public function getFloat(string $key, ?string $scope = null): float
    {
        $value = $this->get($key, $scope);
        if (!is_numeric($value)) {
            throw new ConfigException(sprintf('Configuration key "%s" is not a float', $key), sprintf('Expected float, got %s', get_debug_type($value)), 'Ensure your config file returns a float for this key.');
        }
        return (float) $value;
    }

    public function getArray(string $key, ?string $scope = null): array
    {
        $value = $this->get($key, $scope);
        if (!is_array($value)) {
            throw new ConfigException(sprintf('Configuration key "%s" is not an array', $key), sprintf('Expected array, got %s', get_debug_type($value)), 'Ensure your config file returns an array for this key.');
        }
        return $value;
    }

    public function all(?string $scope = null): array
    {
        throw new ConfigException('MarkoConfigRepositoryAdapter does not support all().', "Zoosper's own ConfigRepository does not expose its full merged config array.", 'Access specific keys via get()/getString()/getInt()/etc. instead.');
    }

    public function withScope(string $scope): ConfigRepositoryInterface
    {
        throw new ConfigException('MarkoConfigRepositoryAdapter does not support withScope().', "Zoosper's own config system has no per-store/scope resolution concept yet.", 'Access unscoped keys directly via get()/getString()/etc.');
    }
}
