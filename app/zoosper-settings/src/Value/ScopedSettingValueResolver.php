<?php

declare(strict_types=1);

namespace Zoosper\Settings\Value;

use Zoosper\Settings\Persistence\ScopedSettingStoreInterface;
use Zoosper\ScopedConfig\ScopeContext;
use Zoosper\ScopedConfig\ScopeType;
use Zoosper\Settings\Definition\SettingDefinition;

/** Resolves scoped DB overrides before the Phase 9B project/default fallback. */
final readonly class ScopedSettingValueResolver
{
    public function __construct(
        private ScopedSettingStoreInterface $scoped,
        private SettingValueResolver $fallback,
    ) {
    }

    public function resolve(SettingDefinition $definition, ScopeContext $context): SettingValue
    {
        $resolved = $this->scoped->resolve($definition->path, $context);
        /** @var ScopeType|null $resolvedScope */
        $resolvedScope = $resolved['resolvedScope'];
        if ($resolvedScope === null) {
            return $this->fallback->resolve($definition);
        }

        $source = $resolvedScope === $this->requestedScope($context) ? 'database' : 'inherited';

        return new SettingValue(
            path: $definition->path,
            value: $definition->secret ? null : $resolved['value'],
            source: $source,
            readOnly: $definition->readOnly,
            secret: $definition->secret,
            explanation: $source === 'database'
                ? 'Using the value saved at the current scope.'
                : 'Inherited from the ' . $resolvedScope->value . ' scope.',
        );
    }

    private function requestedScope(ScopeContext $context): ScopeType
    {
        if ($context->siteId !== null) {
            return ScopeType::Site;
        }
        if ($context->storeCode !== null) {
            return ScopeType::Store;
        }
        if ($context->websiteCode !== null) {
            return ScopeType::Website;
        }

        return ScopeType::Default;
    }
}
