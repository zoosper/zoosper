<?php
declare(strict_types=1);
namespace Zoosper\Cache\Driver;
use Marko\Cache\Contracts\CacheInterface as MarkoCacheInterface;
use Zoosper\Cache\Contract\CacheInterface;
final readonly class MarkoCacheAdapter implements CacheInterface
{
    public function __construct(private MarkoCacheInterface $driver) {}
    public function get(string $key, mixed $default = null): mixed { return $this->driver->get($key, $default); }
    public function set(string $key, mixed $value, ?int $ttl = null): bool { return $this->driver->set($key, $value, $ttl); }
    public function has(string $key): bool { return $this->driver->has($key); }
    public function delete(string $key): bool { return $this->driver->delete($key); }
    public function clear(): bool { return $this->driver->clear(); }
    public function getMultiple(array $keys, mixed $default = null): iterable { return $this->driver->getMultiple($keys, $default); }
    public function setMultiple(array $values, ?int $ttl = null): bool { return $this->driver->setMultiple($values, $ttl); }
    public function deleteMultiple(array $keys): bool { return $this->driver->deleteMultiple($keys); }
    public function increment(string $key, int $ttl): int { return $this->driver->increment($key, $ttl); }
    public function markoDriver(): MarkoCacheInterface { return $this->driver; }
}
