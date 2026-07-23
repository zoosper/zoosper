<?php

declare(strict_types=1);

namespace Zoosper\Core\Config;

/**
 * Result of an ordered layered config merge.
 */
final readonly class LayeredConfigResult
{
    /**
     * @param array<string,mixed> $config
     * @param list<string> $sources
     */
    public function __construct(
        public array $config,
        public array $sources,
    ) {
    }
}
