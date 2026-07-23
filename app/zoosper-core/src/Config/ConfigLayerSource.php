<?php

declare(strict_types=1);

namespace Zoosper\Core\Config;

/**
 * Named PHP config file layer.
 */
final readonly class ConfigLayerSource
{
    public function __construct(
        public string $source,
        public string $path,
    ) {
        if ($source === '') {
            throw new \InvalidArgumentException('Config layer source name cannot be empty.');
        }

        if ($path === '') {
            throw new \InvalidArgumentException('Config layer source path cannot be empty.');
        }
    }
}
