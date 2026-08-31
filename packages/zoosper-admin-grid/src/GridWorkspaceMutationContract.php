<?php

declare(strict_types=1);

namespace Zoosper\AdminGrid;

/** Stable action names used by feature-specific, CSRF-protected controllers. */
final class GridWorkspaceMutationContract
{
    public const SAVE_COLUMNS = 'save_columns';
    public const RESET_COLUMNS = 'reset_columns';
    public const SAVE_VIEW = 'save_view';
    public const DELETE_VIEW = 'delete_view';
    public const SET_DEFAULT_VIEW = 'set_default_view';

    private function __construct()
    {
    }
}











