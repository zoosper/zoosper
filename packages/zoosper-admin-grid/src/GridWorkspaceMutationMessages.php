<?php

declare(strict_types=1);

namespace Zoosper\AdminGrid;

/** User-facing success messages for the stable workspace actions. */
final class GridWorkspaceMutationMessages
{
    public static function forAction(string $action): string
    {
        return match ($action) {
            GridWorkspaceMutationContract::SAVE_COLUMNS => 'Grid columns saved.',
            GridWorkspaceMutationContract::RESET_COLUMNS => 'Grid columns reset to default.',
            GridWorkspaceMutationContract::SAVE_VIEW => 'Grid view saved.',
            GridWorkspaceMutationContract::DELETE_VIEW => 'Grid view deleted.',
            GridWorkspaceMutationContract::SET_DEFAULT_VIEW => 'Default Grid view updated.',
            default => throw new \InvalidArgumentException(
                'Unsupported Grid workspace mutation action.',
            ),
        };
    }

    private function __construct()
    {
    }
}











