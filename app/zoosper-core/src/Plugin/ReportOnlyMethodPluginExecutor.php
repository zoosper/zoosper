<?php

declare(strict_types=1);

namespace Zoosper\Core\Plugin;

use Throwable;

/**
 * Executes plugins in report-only mode for explicitly allow-listed invocations.
 *
 * The production result is always the baseline result from the final callable.
 * Plugin execution is only observed and recorded when allow-listed.
 */
final readonly class ReportOnlyMethodPluginExecutor
{
    /**
     * @param list<string> $allowedInvocationKeys
     */
    public function __construct(
        private MethodPluginExecutor $executor,
        private MethodPluginReportSinkInterface $sink,
        private array $allowedInvocationKeys = [],
    ) {
    }

    /**
     * @param callable(MethodInvocation): mixed $final
     */
    public function execute(MethodInvocation $invocation, callable $final): mixed
    {
        $baselineResult = $final($invocation);
        $enabled = in_array($invocation->key(), $this->allowedInvocationKeys, true);

        if (!$enabled) {
            $this->sink->record(new MethodPluginReportOnlyResult(
                invocationKey: $invocation->key(),
                enabled: false,
                baselineResult: $baselineResult,
            ));

            return $baselineResult;
        }

        try {
            $pluginResult = $this->executor->execute($invocation, $final);
            $this->sink->record(new MethodPluginReportOnlyResult(
                invocationKey: $invocation->key(),
                enabled: true,
                baselineResult: $baselineResult,
                pluginResult: $pluginResult,
            ));
        } catch (Throwable $exception) {
            $this->sink->record(new MethodPluginReportOnlyResult(
                invocationKey: $invocation->key(),
                enabled: true,
                baselineResult: $baselineResult,
                error: $exception->getMessage(),
            ));
        }

        return $baselineResult;
    }
}
