<?php

declare(strict_types=1);

namespace Zoosper\Grid\BulkAction;

use InvalidArgumentException;

/** Immutable input to the shared server-side bulk-action boundary. */
final readonly class GridBulkActionRequest
{
    /** @param list<int|string> $selectedIdentities */
    public function __construct(
        public string $gridKey,
        public string $actionId,
        public array $selectedIdentities,
    ) {
        if (trim($gridKey) === '') {
            throw new InvalidArgumentException('Grid bulk-action request requires a Grid key.');
        }
        if (trim($actionId) === '') {
            throw new InvalidArgumentException('Grid bulk-action request requires an action ID.');
        }
    }
}
