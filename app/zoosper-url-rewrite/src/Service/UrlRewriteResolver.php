<?php

declare(strict_types=1);

namespace Zoosper\UrlRewrite\Service;

use Zoosper\UrlRewrite\Model\UrlRewrite;
use Zoosper\UrlRewrite\Repository\UrlRewriteRepository;

/**
 * Resolves frontend request paths to URL rewrite records.
 */
final readonly class UrlRewriteResolver
{
    public function __construct(private UrlRewriteRepository $rewrites)
    {
    }

    /**
     * Resolve an active URL rewrite for the supplied site and path.
     */
    public function resolve(int $siteId, string $requestPath): ?UrlRewrite
    {
        return $this->rewrites->findActiveByRequestPath($siteId, trim($requestPath, '/'));
    }
}
