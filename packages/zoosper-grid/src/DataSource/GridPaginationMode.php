<?php

declare(strict_types=1);

namespace Zoosper\Grid\DataSource;

enum GridPaginationMode: string
{
    case Numbered = 'numbered';
    case Cursor = 'cursor';
}
