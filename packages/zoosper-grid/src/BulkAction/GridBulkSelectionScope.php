<?php

declare(strict_types=1);

namespace Zoosper\Grid\BulkAction;

/** Defines which records a bulk action is allowed to target. */
enum GridBulkSelectionScope: string
{
    case CURRENT_PAGE = 'current_page';
    case EXPLICIT_IDENTITIES = 'explicit_identities';
    case ALL_MATCHING = 'all_matching';
}
