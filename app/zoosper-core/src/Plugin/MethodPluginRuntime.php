<?php

declare(strict_types=1);

namespace Zoosper\Core\Plugin;

/**
 * Disabled-by-default integration seam for method plugin execution.
 *
 * When disabled, this seam simply calls the final/original callable. When
 * enabled in report-only mode, it delegates to ReportOnlyMethodPluginExecutor
 * and still returns the baseline result to the caller.
 */
final readonly class MethodPluginRuntime
{
    public function __construct(
        private MethodPluginRuntimeConfig $config = new MethodPluginRuntimeConfig(),
        private ?ReportOnlyMethodPluginExecutor $reportOnlyExecutor = null,
    ) {
    }

    /**
     * @param callable(MethodInvocation): mixed $final
     */
    public function execute(MethodInvocation $invocation, callable $final): mixed
    {
        if (!$this->config->enabled || !$this->config->reportOnly || !$this->config->allows($invocation)) {
            return $final($invocation);
        }

        if ($this->reportOnlyExecutor === null) {
            return $final($invocation);
        }

        return $this->reportOnlyExecutor->execute($invocation, $final);
    }
}
