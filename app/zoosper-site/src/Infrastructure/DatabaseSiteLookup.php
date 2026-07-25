<?php

declare(strict_types=1);

namespace Zoosper\Site\Infrastructure;

use Zoosper\Core\Site\ResolvedSite;
use Zoosper\Core\Site\SiteLookupInterface;
use Zoosper\Site\Repository\SiteRepository;

/**
 * Site-module adapter that keeps SiteRepository usage outside zoosper-core.
 *
 * The adapter is intentionally defensive about repository method names so it can
 * support the current SiteRepository shape while the runtime cutover is prepared.
 */
final class DatabaseSiteLookup implements SiteLookupInterface
{
    public function __construct(
        private readonly SiteRepository $sites,
    ) {
    }

    public function findByHost(string $host): ?ResolvedSite
    {
        foreach (['findByHost', 'findByDomain', 'findActiveByHost'] as $method) {
            if (method_exists($this->sites, $method)) {
                return $this->toResolvedSite($this->sites->{$method}($host));
            }
        }

        return null;
    }

    public function findByCode(string $code): ?ResolvedSite
    {
        foreach (['findByCode', 'findOneByCode'] as $method) {
            if (method_exists($this->sites, $method)) {
                return $this->toResolvedSite($this->sites->{$method}($code));
            }
        }

        return null;
    }

    public function findDefault(): ?ResolvedSite
    {
        foreach (['findDefault', 'getDefault', 'default'] as $method) {
            if (method_exists($this->sites, $method)) {
                return $this->toResolvedSite($this->sites->{$method}());
            }
        }

        return null;
    }

    private function toResolvedSite(mixed $site): ?ResolvedSite
    {
        if ($site === null) {
            return null;
        }

        if ($site instanceof ResolvedSite) {
            return $site;
        }

        if (is_array($site)) {
            return new ResolvedSite(
                $site['id'] ?? null,
                (string) ($site['code'] ?? ''),
                (string) ($site['name'] ?? ''),
                (string) ($site['base_url'] ?? $site['baseUrl'] ?? ''),
                (bool) ($site['is_active'] ?? $site['isActive'] ?? true),
            );
        }

        if (is_object($site)) {
            return new ResolvedSite(
                $this->read($site, ['id', 'siteId']),
                (string) ($this->read($site, ['code']) ?? ''),
                (string) ($this->read($site, ['name']) ?? ''),
                (string) ($this->read($site, ['baseUrl', 'base_url']) ?? ''),
                (bool) ($this->read($site, ['isActive', 'is_active', 'active']) ?? true),
            );
        }

        return null;
    }

    /**
     * @param list<string> $names
     */
    private function read(object $site, array $names): mixed
    {
        foreach ($names as $name) {
            if (isset($site->{$name})) {
                return $site->{$name};
            }

            $getter = 'get' . ucfirst($name);
            if (method_exists($site, $getter)) {
                return $site->{$getter}();
            }
        }

        return null;
    }
}
