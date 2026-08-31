<?php

declare(strict_types=1);

namespace Zoosper\Grid\BulkAction;

/** Implemented by feature modules for one exact Grid/action pair. */
interface GridBulkActionExecutorInterface
{
    public function gridKey(): string;

    public function actionId(): string;

    public function execute(
        GridBulkActionDefinition $definition,
        GridBulkSelection $selection,
        GridBulkExecutionContext $context,
    ): GridBulkActionExecutionResult;
}











