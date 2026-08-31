<?php

declare(strict_types=1);

/**
 * Public frontend page cache. Uses whichever backend config/cache.php
 * selects (file or Redis) — this file only controls WHETHER and for HOW
 * LONG frontend page responses are cached, not the underlying storage.
 *
 * Disabled by default, matching the same "off by default" discipline
 * already applied to rate limiting and other behaviourally-risky features
 * this session — a page cache that serves stale or wrong content is worse
 * than no cache at all, so this must be a deliberate opt-in.
 *
 * See Zoosper\Core\Routing\CachingFallbackHandler's own docblock for the
 * full safety model and honestly-stated current limitations (no
 * query-string variance; single-theme-per-site assumption).
 */
return [
    'enabled' => filter_var(env('PAGE_CACHE_ENABLED', false), FILTER_VALIDATE_BOOLEAN),
    'ttl' => (int) env('PAGE_CACHE_TTL', 300),
];








