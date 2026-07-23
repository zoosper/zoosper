<?php

declare(strict_types=1);

namespace Zoosper\Core\Plugin;

/**
 * In-memory report sink for tests, smoke tools, and future diagnostics.
 */
final class InMemoryMethodPluginReportSink implements MethodPluginReportSinkInterface
{
    /** @var list<MethodPluginReportOnlyResult> */
    private array $results = [];

    public function record(MethodPluginReportOnlyResult $result): void
    {
        $this->results[] = $result;
    }

    /**
     * @return list<MethodPluginReportOnlyResult>
     */
    public function results(): array
    {
        return $this->results;
    }
}
