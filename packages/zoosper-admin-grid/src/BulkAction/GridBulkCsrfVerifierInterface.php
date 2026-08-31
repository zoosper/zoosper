<?php

declare(strict_types=1);

namespace Zoosper\AdminGrid\BulkAction;

/** Adapts the host application's CSRF service without coupling Grid to Admin. */
interface GridBulkCsrfVerifierInterface
{
    public function assertValid(string $token): void;
}











