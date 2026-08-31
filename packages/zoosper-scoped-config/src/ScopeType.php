<?php

declare(strict_types=1);

namespace Zoosper\ScopedConfig;

/**
 * The four levels a config value can be set at, from least to most specific.
 * Resolution always tries most-specific first: SITE, then STORE, then
 * WEBSITE, then DEFAULT.
 */
enum ScopeType: string
{
    case Default = 'default';
    case Website = 'website';
    case Store = 'store';
    case Site = 'site';
}











