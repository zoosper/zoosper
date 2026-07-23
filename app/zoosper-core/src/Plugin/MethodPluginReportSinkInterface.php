<?php

declare(strict_types=1);

namespace Zoosper\Core\Plugin;

/**
 * Receives report-only method plugin execution results.
 */
interface MethodPluginReportSinkInterface
{
    public function record(MethodPluginReportOnlyResult $result): void;
}
