<?php

declare(strict_types=1);

use Zoosper\Cache\Contract\CacheInterface;
use Zoosper\Cache\Factory\CacheDriverFactory;
use Zoosper\Core\Cache\CacheKeyBuilder;
use Zoosper\Core\Config\ConfigRepository;
use Zoosper\Core\Http\Request;
use Zoosper\Core\Http\Response;
use Zoosper\Core\Routing\CachingFallbackHandler;
use Zoosper\Core\Routing\FallbackHandlerInterface;
use Zoosper\Core\Site\SiteContext;

/**
 * Proves CachingFallbackHandler's full safety model: caches only GET
 * requests with a real Request + SiteContext + exactly-200 Response, fails
 * open on every unsafe condition, and produces genuinely Marko-cache-key-
 * valid keys (the confirmed ':'/'/' incompatibility — see the class's own
 * docblock).
 *
 * A minimal in-memory fake implementing the REAL, full
 * Marko\Cache\Contracts\CacheInterface contract is used for most tests
 * (fast, precise call-count assertions); one test additionally exercises
 * the REAL FileCacheDriver end-to-end specifically to prove the cache-key
 * compatibility fix works against Marko's actual validation logic, not
 * just the fake's.
 *
 * File placement: app/zoosper-core/tests/Unit/Routing/CachingFallbackHandlerTest.php
 * — uses only namespaced imports, no dirname()-based path resolution needed.
 */
final class FakeMarkoCacheForCachingFallbackHandlerTest implements CacheInterface
{
    /** @var array<string, mixed> */
    public array $store = [];
    public int $getCalls = 0;
    public int $setCalls = 0;

    public function get(string $key, mixed $default = null): mixed
    {
        $this->getCalls++;
        return $this->store[$key] ?? $default;
    }

    public function set(string $key, mixed $value, ?int $ttl = null): bool
    {
        $this->setCalls++;
        $this->store[$key] = $value;
        return true;
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->store);
    }

    public function delete(string $key): bool
    {
        unset($this->store[$key]);
        return true;
    }

    public function clear(): bool
    {
        $this->store = [];
        return true;
    }

    public function getMultiple(array $keys, mixed $default = null): iterable
    {
        $result = [];
        foreach ($keys as $key) {
            $result[$key] = $this->get($key, $default);
        }
        return $result;
    }

    public function setMultiple(array $values, ?int $ttl = null): bool
    {
        foreach ($values as $key => $value) {
            $this->set($key, $value, $ttl);
        }
        return true;
    }

    public function deleteMultiple(array $keys): bool
    {
        foreach ($keys as $key) {
            $this->delete($key);
        }
        return true;
    }

    public function increment(string $key, int $ttl): int
    {
        $value = ((int) ($this->store[$key] ?? 0)) + 1;
        $this->store[$key] = $value;
        return $value;
    }
}

final class FakeInnerFallbackHandlerForCachingFallbackHandlerTest implements FallbackHandlerInterface
{
    public int $handleCalls = 0;

    public function __construct(private readonly mixed $result)
    {
    }

    public function supports(object $request): bool
    {
        return true;
    }

    public function handle(object $request): mixed
    {
        $this->handleCalls++;
        return $this->result;
    }
}

function cachingFallbackHandlerTestSiteContext(): SiteContext
{
    return new SiteContext(
        websiteCode: 'site_a',
        websiteName: 'SITE_A',
        storeCode: 'site_a',
        storeName: 'SITE_A',
        storeViewCode: 'site_a_view',
        storeViewName: 'SITE_A View',
        locale: 'en_AU',
        currency: 'AUD',
        baseUrl: 'https://site-a.example',
        pathPrefix: '',
    );
}

function cachingFallbackHandlerTestRequest(string $method = 'GET', ?SiteContext $siteContext = null, string $path = '/some-page'): Request
{
    $request = new Request(method: $method, path: $path, host: 'site-a.example');

    return $siteContext !== null ? $request->withSiteContext($siteContext) : $request;
}

it('when disabled, delegates straight through and never touches the cache', function (): void {
    $cache = new FakeMarkoCacheForCachingFallbackHandlerTest();
    $inner = new FakeInnerFallbackHandlerForCachingFallbackHandlerTest(Response::html('hello', 200));

    $handler = new CachingFallbackHandler($inner, $cache, new CacheKeyBuilder(), enabled: false, ttlSeconds: 300);
    $request = cachingFallbackHandlerTestRequest(siteContext: cachingFallbackHandlerTestSiteContext());

    $handler->handle($request);

    expect($inner->handleCalls)->toBe(1);
    expect($cache->getCalls)->toBe(0);
    expect($cache->setCalls)->toBe(0);
});

it('when enabled, caches a 200 response and serves the second identical request from cache without calling inner again', function (): void {
    $cache = new FakeMarkoCacheForCachingFallbackHandlerTest();
    $inner = new FakeInnerFallbackHandlerForCachingFallbackHandlerTest(Response::html('rendered page body', 200));

    $handler = new CachingFallbackHandler($inner, $cache, new CacheKeyBuilder(), enabled: true, ttlSeconds: 300);
    $siteContext = cachingFallbackHandlerTestSiteContext();

    $first = $handler->handle(cachingFallbackHandlerTestRequest(siteContext: $siteContext));
    expect($inner->handleCalls)->toBe(1);
    expect($cache->setCalls)->toBe(1);
    expect($first)->toBeInstanceOf(Response::class);
    expect($first->body())->toBe('rendered page body');
    // Confirms the Cache-Control policy header was genuinely attached.
    expect($first->headers())->toHaveKey('Cache-Control');

    $second = $handler->handle(cachingFallbackHandlerTestRequest(siteContext: $siteContext));

    // THE CRITICAL ASSERTION: inner was NOT called again — served from cache.
    expect($inner->handleCalls)->toBe(1);
    expect($second->body())->toBe('rendered page body');
});

it('never caches a non-200 response', function (): void {
    $cache = new FakeMarkoCacheForCachingFallbackHandlerTest();
    $inner = new FakeInnerFallbackHandlerForCachingFallbackHandlerTest(Response::html('not found', 404));

    $handler = new CachingFallbackHandler($inner, $cache, new CacheKeyBuilder(), enabled: true, ttlSeconds: 300);
    $handler->handle(cachingFallbackHandlerTestRequest(siteContext: cachingFallbackHandlerTestSiteContext()));

    expect($cache->setCalls)->toBe(0);
});

it('never caches a non-Response result (e.g. null, the real "not found" signal)', function (): void {
    $cache = new FakeMarkoCacheForCachingFallbackHandlerTest();
    $inner = new FakeInnerFallbackHandlerForCachingFallbackHandlerTest(null);

    $handler = new CachingFallbackHandler($inner, $cache, new CacheKeyBuilder(), enabled: true, ttlSeconds: 300);
    $result = $handler->handle(cachingFallbackHandlerTestRequest(siteContext: cachingFallbackHandlerTestSiteContext()));

    expect($result)->toBeNull();
    expect($cache->setCalls)->toBe(0);
});

it('bypasses caching entirely for non-GET requests, always delegating to inner', function (): void {
    $cache = new FakeMarkoCacheForCachingFallbackHandlerTest();
    $inner = new FakeInnerFallbackHandlerForCachingFallbackHandlerTest(Response::html('posted', 200));

    $handler = new CachingFallbackHandler($inner, $cache, new CacheKeyBuilder(), enabled: true, ttlSeconds: 300);
    $siteContext = cachingFallbackHandlerTestSiteContext();

    $handler->handle(cachingFallbackHandlerTestRequest(method: 'POST', siteContext: $siteContext));
    $handler->handle(cachingFallbackHandlerTestRequest(method: 'POST', siteContext: $siteContext));

    expect($inner->handleCalls)->toBe(2);
    expect($cache->getCalls)->toBe(0);
    expect($cache->setCalls)->toBe(0);
});

it('fails open safely (no exception, no caching) when the request has no SiteContext at all', function (): void {
    $cache = new FakeMarkoCacheForCachingFallbackHandlerTest();
    $inner = new FakeInnerFallbackHandlerForCachingFallbackHandlerTest(Response::html('rendered anyway', 200));

    $handler = new CachingFallbackHandler($inner, $cache, new CacheKeyBuilder(), enabled: true, ttlSeconds: 300);
    $result = $handler->handle(cachingFallbackHandlerTestRequest(siteContext: null));

    expect($inner->handleCalls)->toBe(1);
    expect($cache->setCalls)->toBe(0);
    expect($result)->toBeInstanceOf(Response::class);
});

it('produces a genuinely Marko-cache-key-valid key, proven end-to-end against the REAL FileCacheDriver (not the fake)', function (): void {
    $realConfig = ConfigRepository::fromArray([
        'cache' => ['driver' => 'file', 'path' => 'var/cache/zoosper-caching-fallback-handler-test-' . bin2hex(random_bytes(4)), 'default_ttl' => 60],
        'encryption' => ['key' => '', 'cipher' => 'aes-256-gcm'],
    ]);
    $realCache = (new CacheDriverFactory($realConfig, dirname(__DIR__, 5)))->create();
    $inner = new FakeInnerFallbackHandlerForCachingFallbackHandlerTest(Response::html('real cache driver body', 200));

    $handler = new CachingFallbackHandler($inner, $realCache, new CacheKeyBuilder(), enabled: true, ttlSeconds: 60);
    $siteContext = cachingFallbackHandlerTestSiteContext();

    // The critical proof: this must NOT throw Marko\Cache\Exceptions\
    // InvalidKeyException, confirming the ':'/'/' -> hash fix genuinely
    // works against Marko's REAL, installed validation logic.
    $first = $handler->handle(cachingFallbackHandlerTestRequest(siteContext: $siteContext));
    $second = $handler->handle(cachingFallbackHandlerTestRequest(siteContext: $siteContext));

    expect($first->body())->toBe('real cache driver body');
    expect($second->body())->toBe('real cache driver body');
    expect($inner->handleCalls)->toBe(1); // second call served from the REAL cache
});

it('varies cache key when query string differs', function (): void {
    $cache = new FakeMarkoCacheForCachingFallbackHandlerTest();
    $inner = new FakeInnerFallbackHandlerForCachingFallbackHandlerTest(Response::html('page body', 200));

    $handler = new CachingFallbackHandler($inner, $cache, new CacheKeyBuilder(), enabled: true, ttlSeconds: 300);
    $siteContext = cachingFallbackHandlerTestSiteContext();

    $req1 = (new Request(method: 'GET', path: '/page', host: 'site-a.example', queryString: 'page=1'))
        ->withSiteContext($siteContext);
    $req2 = (new Request(method: 'GET', path: '/page', host: 'site-a.example', queryString: 'page=2'))
        ->withSiteContext($siteContext);

    $handler->handle($req1);
    $handler->handle($req2);

    expect($inner->handleCalls)->toBe(2)
        ->and($cache->setCalls)->toBe(2);
});










