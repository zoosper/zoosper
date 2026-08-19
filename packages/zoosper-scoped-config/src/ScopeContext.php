<?php

declare(strict_types=1);

namespace Zoosper\ScopedConfig;

/**
 * Identifies the (website, store, site) coordinates to resolve scoped config
 * against, mirroring Zoosper's own Site model (which is itself a flattened
 * store-view: every Site row already carries websiteCode/storeCode/
 * storeViewCode). There are no separate website/store tables in this schema,
 * so a "website" or "store" scope is addressed by the CODE string shared
 * across every Site row that belongs to it, and a "site" scope (the most
 * specific level, equivalent to Magento's store-view) is addressed by the
 * Site's own integer id.
 *
 * All three fields are optional: pass only what you have. A request handled
 * with no resolved Site context at all simply resolves against 'default'.
 */
final readonly class ScopeContext
{
    public function __construct(
        public ?int $siteId = null,
        public ?string $storeCode = null,
        public ?string $websiteCode = null,
    ) {
    }

    public static function default(): self
    {
        return new self();
    }

    /**
     * Build a context from a Site-shaped array without a hard dependency on
     * the Site feature module's own model class (core must not import
     * feature-module classes — the same boundary enforced by
     * CoreDecouplingArchitectureTest, Phase 1.99).
     *
     * Note: the specific feature-module class name is deliberately NOT
     * spelled out as a literal namespace path in this docblock — writing it
     * out as plain text previously tripped that same architecture guard's
     * broad substring scan, which checks for the forbidden namespace prefix
     * ANYWHERE in a core file, including comments, not just in `use`
     * statements or actual code references.
     *
     * @param array{id?: int|string|null, storeCode?: string|null, websiteCode?: string|null} $site
     */
    public static function fromSiteArray(array $site): self
    {
        $siteId = isset($site['id']) && $site['id'] !== null ? (int) $site['id'] : null;
        $storeCode = isset($site['storeCode']) && $site['storeCode'] !== '' ? (string) $site['storeCode'] : null;
        $websiteCode = isset($site['websiteCode']) && $site['websiteCode'] !== '' ? (string) $site['websiteCode'] : null;

        return new self($siteId, $storeCode, $websiteCode);
    }
}
