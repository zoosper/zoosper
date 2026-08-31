<?php

declare(strict_types=1);

namespace Zoosper\Core\Entity\Lifecycle;

/** Operator-facing entity lifecycle operations. */
enum EntityLifecycleOperation: string
{
    case Archive = 'archive';
    case Disable = 'disable';
    case Delete = 'delete';
}










