<?php
declare(strict_types=1);
namespace Zoosper\Cache\Contract;
interface CacheInterface
{
    public function get(string $key, mixed $default = null): mixed;
    public function set(string $key, mixed $value, ?int $ttl = null): bool;
    public function has(string $key): bool;
    public function delete(string $key): bool;
    public function clear(): bool;
    public function getMultiple(array $keys, mixed $default = null): iterable;
    public function setMultiple(array $values, ?int $ttl = null): bool;
    public function deleteMultiple(array $keys): bool;
    public function increment(string $key, int $ttl): int;
}











