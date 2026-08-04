<?php

declare(strict_types=1);

namespace Zoosper\Grid\BulkAction;

use InvalidArgumentException;

/** Validates definition, execution boundary and selection before delegation. */
final readonly class GridBulkActionDispatcher
{
    public function __construct(
        private GridBulkActionRegistry $definitions,
        private GridBulkActionExecutorRegistry $executors,
    ) {
    }

    public function dispatch(GridBulkActionRequest $request): GridBulkActionExecutionResult
    {
        $definition = $this->definitions->require($request->gridKey, $request->actionId);
        if (!in_array($definition->executionType, [
            GridBulkExecutionType::SERVER_DOWNLOAD,
            GridBulkExecutionType::SERVER_MUTATION,
            GridBulkExecutionType::REMOTE_MUTATION,
        ], true)) {
            throw new InvalidArgumentException(
                sprintf('Grid bulk action "%s" is not server executable.', $definition->id),
            );
        }
        if ($definition->selectionScope !== GridBulkSelectionScope::EXPLICIT_IDENTITIES) {
            throw new InvalidArgumentException(
                sprintf('Grid bulk action "%s" requires an unsupported selection scope.', $definition->id),
            );
        }

        $selection = new GridBulkSelection(
            $request->selectedIdentities,
            $definition->maximumSelection,
        );

        return $this->executors
            ->require($request->gridKey, $request->actionId)
            ->execute($definition, $selection);
    }
}
