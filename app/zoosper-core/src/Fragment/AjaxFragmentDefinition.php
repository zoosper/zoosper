<?php

declare(strict_types=1);

namespace Zoosper\Core\Fragment;

use Zoosper\Core\Cache\HttpCachePolicy;

/**
 * Describes a future AJAX fragment endpoint without coupling to a concrete router.
 *
 * The route path is supplied by module-owned routes later; this value object
 * carries the fragment code, cache policy and SEO guidance. Fragments must not
 * be used for SEO-critical main content such as page title, primary body,
 * canonical URL, hreflang or structured data.
 */
final readonly class AjaxFragmentDefinition
{
    public function __construct(
        public string $code,
        public string $routeName,
        public HttpCachePolicy $policy,
        public bool $seoCritical = false,
        public string $description = '',
    ) {
    }

    /**
     * Return whether this fragment is safe to defer behind AJAX.
     */
    public function isAjaxSafe(): bool
    {
        return !$this->seoCritical;
    }
}
