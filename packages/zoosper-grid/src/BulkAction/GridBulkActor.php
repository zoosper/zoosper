<?php

declare(strict_types=1);

namespace Zoosper\Grid\BulkAction;

use InvalidArgumentException;

/** Authenticated actor identity carried to feature executors and audit events. */
final readonly class GridBulkActor
{
    public function __construct(
        public int $adminUserId,
        public ?string $email = null,
    ) {
        if ($adminUserId < 1) {
            throw new InvalidArgumentException('Grid bulk-action actor requires a positive admin-user ID.');
        }

        if ($email !== null && trim($email) === '') {
            throw new InvalidArgumentException('Grid bulk-action actor email cannot be empty when supplied.');
        }
    }
}
