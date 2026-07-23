<?php

declare(strict_types=1);

namespace Zoosper\Core\Plugin;

/**
 * Runtime controls for method plugin execution.
 *
 * The default state is intentionally disabled so no production service can be
 * intercepted until a later phase explicitly opts in a safe invocation key.
 */
final readonly class MethodPluginRuntimeConfig
{
    /**
     * @param list<string> $reportOnlyInvocationKeys
     */
    public function __construct(
        public bool $enabled = false,
        public bool $reportOnly = true,
        public array $reportOnlyInvocationKeys = [],
    ) {
    }

    public static function disabled(): self
    {
        return new self(enabled: false, reportOnly: true, reportOnlyInvocationKeys: []);
    }

    /**
     * @param list<string> $invocationKeys
     */
    public static function reportOnly(array $invocationKeys): self
    {
        return new self(enabled: true, reportOnly: true, reportOnlyInvocationKeys: array_values($invocationKeys));
    }

    public function allows(MethodInvocation $invocation): bool
    {
        return $this->enabled
            && $this->reportOnly
            && in_array($invocation->key(), $this->reportOnlyInvocationKeys, true);
    }
}
