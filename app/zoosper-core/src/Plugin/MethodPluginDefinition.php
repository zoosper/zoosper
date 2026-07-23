<?php

declare(strict_types=1);

namespace Zoosper\Core\Plugin;

/**
 * Declarative plugin definition for one subject method.
 */
final readonly class MethodPluginDefinition
{
    public function __construct(
        public string $subject,
        public string $method,
        public string $pluginClass,
        public int $sortOrder = 100,
        public bool $enabled = true,
    ) {
    }

    public function key(): string
    {
        return $this->subject . '::' . $this->method;
    }
}
