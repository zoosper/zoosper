<?php

declare(strict_types=1);

namespace Zoosper\Core\Plugin;

/**
 * Result of report-only plugin execution.
 */
final readonly class MethodPluginReportOnlyResult
{
    public function __construct(
        public string $invocationKey,
        public bool $enabled,
        public mixed $baselineResult,
        public mixed $pluginResult = null,
        public ?string $error = null,
    ) {
    }

    public function changed(): bool
    {
        return $this->enabled && $this->error === null && $this->baselineResult !== $this->pluginResult;
    }
}
