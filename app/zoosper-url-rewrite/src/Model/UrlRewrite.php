<?php

declare(strict_types=1);

namespace Zoosper\UrlRewrite\Model;

/**
 * Immutable URL rewrite record.
 *
 * URL rewrites map a user-facing request path to an internal target path or
 * entity. They must never be used to store payment data or other PCI-sensitive
 * information.
 */
final readonly class UrlRewrite
{
    public function __construct(
        public int $id,
        public int $siteId,
        public string $requestPath,
        public string $targetPath,
        public string $entityType,
        public ?int $entityId,
        public int $redirectType,
        public bool $isActive,
    ) {
    }
}
