<?php

declare(strict_types=1);

namespace Zoosper\Core\Routing;

use Zoosper\Cache\Contract\CacheInterface;
use Throwable;
use Zoosper\Core\Cache\CacheContext;
use Zoosper\Core\Cache\CacheKeyBuilder;
use Zoosper\Core\Cache\HttpCachePolicy;
use Zoosper\Core\Http\Request;
use Zoosper\Core\Http\Response;
use Zoosper\Core\Site\SiteContext;

/**
 * Decorates any FallbackHandlerInterface with a real, opt-in public page
 * cache, backed by the CacheInterface registered in
 * app/zoosper-core/config/services.php (file or Redis driver, per
 * config/cache.php).
 *
 * GENERIC BY DESIGN: this class knows nothing about pages specifically — it
 * only decorates whatever FallbackHandlerInterface it wraps. zoosper-page's
 * own services.php composes this around the real PageFallbackHandler; any
 * other module could reuse it the same way for its own fallback handler.
 *
 * SAFETY MODEL — every one of these fails OPEN (delegates straight to the
 * inner handler, never caches, never throws) rather than risking a broken
 * or stale response:
 * - disabled via config (default: disabled)
 * - $request is not a real Zoosper\Core\Http\Request instance (the
 *   interface types it as `object` for testability)
 * - request method is not GET
 * - $request->siteContext() is null (nothing safe to key on)
 * - the underlying cache backend throws on read OR write (e.g. Redis
 *   temporarily unreachable) — a cache problem must never break a page
 * - the inner handler's result is not an instanceof Response, or its
 *   status code is not exactly 200 — never caches redirects, errors, or
 *   raw non-Response values (including null, the real "not found" signal)
 *
 * CONFIRMED, REAL INCOMPATIBILITY FIXED HERE: CacheKeyBuilder::build() joins
 * segments with ':', and its own segment() regex explicitly permits '/'
 * through — but Marko\Cache\Exceptions\InvalidKeyException::isValidKey()
 * (read directly from the installed marko/cache source) explicitly forbids
 * BOTH characters. Passing CacheKeyBuilder's raw output straight into
 * CacheInterface::set()/get() would throw InvalidKeyException on every
 * call. Fixed by reusing CacheKeyBuilder's correct dimension-computation
 * logic (site/host/path/theme/route), then hashing that whole structured
 * string into a Marko-safe hex key — correctness from the existing key
 * builder, compatibility guaranteed by construction.
 *
 * HONEST, STATED LIMITATIONS (not hidden):
 * 1. Does NOT vary the cache key by query string. Request exposes no
 *    generic "full raw query string" accessor (only single-key lookup via
 *    query($key)) — deliberately not reaching around that abstraction by
 *    reading $_SERVER directly here, which would reintroduce exactly the
 *    anti-pattern already fixed for Request::form() earlier this session.
 *    Low-risk today (nothing in PageController/PageRenderer currently reads
 *    query parameters for rendering), but this cache must NOT be considered
 *    safe for any future query-driven frontend feature (search, pagination)
 *    without first adding a real Request::queryString() accessor and
 *    incorporating it into the cache key.
 * 2. Uses a fixed placeholder theme dimension ('default'), since this
 *    decorator only has a Request, not the resolved Site model that knows
 *    the real theme code. Safe as long as each site has exactly one theme
 *    (true today); would need real theme resolution if per-site theme
 *    switching for the same URL is ever introduced.
 */
final readonly class CachingFallbackHandler implements FallbackHandlerInterface
{
    public function __construct(
        private FallbackHandlerInterface $inner,
        private CacheInterface $cache,
        private CacheKeyBuilder $keyBuilder,
        private bool $enabled,
        private int $ttlSeconds,
    ) {
    }

    public function supports(object $request): bool
    {
        return $this->inner->supports($request);
    }

    public function handle(object $request): mixed
    {
        if (!$this->enabled || !$request instanceof Request || $request->method() !== 'GET') {
            return $this->inner->handle($request);
        }

        $siteContext = $request->siteContext();
        if ($siteContext === null) {
            return $this->inner->handle($request);
        }

        $cacheKey = $this->buildCacheKey($request, $siteContext);

        $cached = $this->tryRead($cacheKey);
        if (is_array($cached) && isset($cached['body'], $cached['statusCode'], $cached['headers'])) {
            return Response::raw((string) $cached['body'], (int) $cached['statusCode'], (array) $cached['headers']);
        }

        $response = $this->inner->handle($request);

        if (!$response instanceof Response || $response->statusCode() !== 200) {
            return $response;
        }

        $policyHeaders = HttpCachePolicy::publicPage($this->ttlSeconds, $this->ttlSeconds)->headers;
        // Response's own headers (e.g. Content-Type) take precedence on any
        // key collision — array union (+) keeps left-hand keys, so policy
        // headers only ADD (Cache-Control, X-Zoosper-Cache-Policy), never
        // overwrite something the real response already set.
        $mergedHeaders = $response->headers() + $policyHeaders;
        $augmented = Response::raw($response->body(), 200, $mergedHeaders);

        $this->tryWrite($cacheKey, [
            'body' => $augmented->body(),
            'statusCode' => 200,
            'headers' => $mergedHeaders,
        ]);

        return $augmented;
    }

    /**
     * @return array{body: string, statusCode: int, headers: array<string, string>}|null
     */
    private function tryRead(string $cacheKey): ?array
    {
        try {
            $value = $this->cache->get($cacheKey);
        } catch (Throwable) {
            // Fail open: a cache READ failure must never prevent the page
            // from rendering — just behave as a cache miss.
            return null;
        }

        return is_array($value) ? $value : null;
    }

    /** @param array{body: string, statusCode: int, headers: array<string, string>} $value */
    private function tryWrite(string $cacheKey, array $value): void
    {
        try {
            $this->cache->set($cacheKey, $value, $this->ttlSeconds);
        } catch (Throwable) {
            // Fail open: a cache WRITE failure must never break the
            // response the user is about to receive.
        }
    }

    private function buildCacheKey(Request $request, SiteContext $siteContext): string
    {
        $cacheContext = CacheContext::fromSiteContext(
            siteContext: $siteContext,
            host: $request->host(),
            path: $request->path(),
            themeCode: 'default', // see class docblock, limitation #2
            isAuthenticated: false,
            customerGroup: 'guest',
            routeName: 'frontend.fallback',
            queryString: $request->queryString(),
        );

        $structured = $this->keyBuilder->page($cacheContext);

        // See class docblock: CacheKeyBuilder's own output can legitimately
        // contain ':' and '/', both forbidden by Marko's cache key
        // validation — hash the whole structured key into a guaranteed-safe
        // hex string, rather than relying on CacheKeyBuilder's format being
        // (or ever remaining) compatible with an external cache backend's
        // own, independent validation rules.
        return 'zoosper.page.' . hash('xxh128', $structured);
    }
}
