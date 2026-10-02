<?php
declare(strict_types=1);
namespace Zoosper\Cache\Driver;
use Marko\Cache\Contracts\CacheInterface as MarkoCacheInterface;
use Zoosper\Cache\Contract\CacheInterface;
final readonly class MarkoCacheAdapter implements CacheInterface
{
    public function __construct(private MarkoCacheInterface $driver) {}
    #[\Override]
    public function get(string $key, mixed $default = null): mixed { return $this->driver->get($key, $default); }
    #[\Override]
    public function set(string $key, mixed $value, ?int $ttl = null): bool { return $this->driver->set($key, $value, $ttl); }
    #[\Override]
    public function has(string $key): bool { return $this->driver->has($key); }
    #[\Override]
    public function delete(string $key): bool { return $this->driver->delete($key); }
    #[\Override]
    public function clear(): bool { return $this->driver->clear(); }
    #[\Override]
    public function getMultiple(array $keys, mixed $default = null): iterable { return $this->driver->getMultiple($keys, $default); }
    #[\Override]
    public function setMultiple(array $values, ?int $ttl = null): bool { return $this->driver->setMultiple($values, $ttl); }
    #[\Override]
    public function deleteMultiple(array $keys): bool { return $this->driver->deleteMultiple($keys); }
    #[\Override]
    public function increment(string $key, int $ttl): int { return $this->driver->increment($key, $ttl); }
    public function markoDriver(): MarkoCacheInterface { return $this->driver; }
}











