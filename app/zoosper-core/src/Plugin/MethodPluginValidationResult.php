<?php

declare(strict_types=1);

namespace Zoosper\Core\Plugin;

/**
 * Validation result for method plugin config entries and definitions.
 */
final readonly class MethodPluginValidationResult
{
    /**
     * @param list<MethodPluginValidationIssue> $issues
     */
    public function __construct(
        public array $issues = [],
    ) {
    }

    public function hasErrors(): bool
    {
        return $this->issues !== [];
    }

    /**
     * @return list<string>
     */
    public function messages(): array
    {
        return array_map(static fn (MethodPluginValidationIssue $issue): string => $issue->message, $this->issues);
    }
}
