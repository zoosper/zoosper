<?php

declare(strict_types=1);

namespace Zoosper\Core\Plugin;

/**
 * One validation issue found in method plugin configuration.
 */
final readonly class MethodPluginValidationIssue
{
    /**
     * @param array<string, mixed> $details
     */
    public function __construct(
        public string $code,
        public string $message,
        public array $details = [],
    ) {
    }
}
