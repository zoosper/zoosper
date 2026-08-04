<?php

declare(strict_types=1);

namespace Zoosper\Grid\BulkAction;

/** Controls whether execution requires an explicit confirmation step. */
enum GridBulkConfirmationPolicy: string
{
    case NONE = 'none';
    case CONFIRM = 'confirm';
    case DESTRUCTIVE = 'destructive';
}
