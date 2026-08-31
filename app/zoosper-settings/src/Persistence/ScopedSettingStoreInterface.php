<?php

declare(strict_types=1);

namespace Zoosper\Settings\Persistence;

use Zoosper\ScopedConfig\ScopeContext;
use Zoosper\ScopedConfig\ScopeType;

interface ScopedSettingStoreInterface
{
    /** @return array{value:?string,resolvedScope:?ScopeType} */
    public function resolve(string $path, ScopeContext $context): array;

    /** @param array<string, string> $values */
    public function writeMany(array $values, ScopeType $scopeType, ?string $scopeKey): void;

    public function clear(string $path, ScopeType $scopeType, ?string $scopeKey): void;
}










