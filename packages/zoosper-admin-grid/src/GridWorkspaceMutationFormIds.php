<?php

declare(strict_types=1);

namespace Zoosper\AdminGrid;

/** Stable DOM IDs for the existing CSRF-protected mutation forms. */
final class GridWorkspaceMutationFormIds
{
    public const SAVE_VIEW = 'grid-workspace-save-view';
    public const SET_DEFAULT_VIEW = 'grid-workspace-set-default-view';
    public const DELETE_VIEW = 'grid-workspace-delete-view';
    public const SAVE_COLUMNS = 'grid-workspace-save-columns';
    public const RESET_COLUMNS = 'grid-workspace-reset-columns';

    public static function forAction(string $action): string
    {
        return match ($action) {
            GridWorkspaceMutationContract::SAVE_VIEW => self::SAVE_VIEW,
            GridWorkspaceMutationContract::SET_DEFAULT_VIEW => self::SET_DEFAULT_VIEW,
            GridWorkspaceMutationContract::DELETE_VIEW => self::DELETE_VIEW,
            GridWorkspaceMutationContract::SAVE_COLUMNS => self::SAVE_COLUMNS,
            GridWorkspaceMutationContract::RESET_COLUMNS => self::RESET_COLUMNS,
            default => throw new \InvalidArgumentException('Unsupported Grid workspace mutation action.'),
        };
    }

    private function __construct() {}
}











