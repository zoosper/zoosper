<?php

declare(strict_types=1);

namespace Zoosper\AdminGrid;

use InvalidArgumentException;

/** Validates the transport-level invariants before feature mutation dispatch. */
final readonly class GridWorkspaceMutationGuard
{
    /** @var list<string> */
    private const ACTIONS = [
        GridWorkspaceMutationContract::SAVE_COLUMNS,
        GridWorkspaceMutationContract::RESET_COLUMNS,
        GridWorkspaceMutationContract::SAVE_VIEW,
        GridWorkspaceMutationContract::DELETE_VIEW,
        GridWorkspaceMutationContract::SET_DEFAULT_VIEW,
    ];

    public function assertAllowed(GridWorkspaceRequest $request): string
    {
        if (!$request->isMutation()) {
            throw new InvalidArgumentException('Grid workspace mutations require POST.');
        }

        $action = $request->action();
        if (!in_array($action, self::ACTIONS, true)) {
            throw new InvalidArgumentException('Unsupported Grid workspace mutation action.');
        }

        return $action;
    }
}











