<?php

declare(strict_types=1);

namespace Zoosper\Core\Plugin;

/**
 * One discovered method-plugin config source.
 */
final readonly class MethodPluginConfigSource
{
    public function __construct(
        public string $source,
        public string $path,
    ) {
    }
}
