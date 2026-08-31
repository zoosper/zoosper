<?php

declare(strict_types=1);

namespace Zoosper\Settings\Scope;

use InvalidArgumentException;
use Zoosper\ScopedConfig\ScopeContext;
use Zoosper\Core\Http\Request;
use Zoosper\Site\Repository\SiteRepository;

/** Validates URL scope selection against real Site records. */
final readonly class SettingsScopeSelection
{
    public function __construct(private SiteRepository $sites)
    {
    }

    /** @return array{context: ScopeContext, label: string, type: string, key: string, websites: list<string>, stores: list<string>, sites: array<int, object>} */
    public function fromRequest(Request $request): array
    {
        return $this->select(
            (string) $request->query('scope', 'default'),
            (string) $request->query('scope_key', ''),
        );
    }

    public function select(string $scope, string $scopeKey = ''): array
    {
        $type = strtolower(trim($scope));
        $key = trim($scopeKey);
        if (!in_array($type, ['default', 'website', 'store', 'site'], true)) {
            throw new InvalidArgumentException("Unsupported settings scope: {$type}");
        }

        $sites = $this->sites->all();
        $websites = $this->unique($sites, 'websiteCode');
        $stores = $this->unique($sites, 'storeCode');

        if ($type === 'default') {
            if ($key !== '') {
                throw new InvalidArgumentException('Default scope does not accept a scope key.');
            }
            return compact('type', 'key', 'sites', 'websites', 'stores') + ['context' => ScopeContext::default(), 'label' => 'Default'];
        }
        if ($key === '') {
            throw new InvalidArgumentException("A scope key is required for {$type} scope.");
        }
        if ($type === 'website') {
            if (!in_array($key, $websites, true)) {
                throw new InvalidArgumentException("Unknown website scope: {$key}");
            }
            return compact('type', 'key', 'sites', 'websites', 'stores') + ['context' => new ScopeContext(websiteCode: $key), 'label' => "Website: {$key}"];
        }
        if ($type === 'store') {
            $site = $this->first($sites, 'storeCode', $key);
            if ($site === null) {
                throw new InvalidArgumentException("Unknown store scope: {$key}");
            }
            return compact('type', 'key', 'sites', 'websites', 'stores') + ['context' => new ScopeContext(storeCode: $key, websiteCode: $site->websiteCode), 'label' => "Store: {$key}"];
        }
        if (!ctype_digit($key) || (int) $key <= 0 || ($site = $this->sites->findById((int) $key)) === null) {
            throw new InvalidArgumentException("Unknown site scope: {$key}");
        }
        return compact('type', 'key', 'sites', 'websites', 'stores') + [
            'context' => new ScopeContext(siteId: $site->id, storeCode: $site->storeCode, websiteCode: $site->websiteCode),
            'label' => "Site: {$site->name}",
        ];
    }

    /** @param array<int, object> $sites @return list<string> */
    private function unique(array $sites, string $property): array
    {
        $values = [];
        foreach ($sites as $site) {
            $value = trim((string) $site->{$property});
            if ($value !== '') {
                $values[$value] = true;
            }
        }
        $values = array_keys($values);
        sort($values);
        return $values;
    }

    /** @param array<int, object> $sites */
    private function first(array $sites, string $property, string $value): ?object
    {
        foreach ($sites as $site) {
            if ((string) $site->{$property} === $value) {
                return $site;
            }
        }
        return null;
    }
}










