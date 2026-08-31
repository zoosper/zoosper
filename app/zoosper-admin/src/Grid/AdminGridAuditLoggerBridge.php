<?php

declare(strict_types=1);

namespace Zoosper\Admin\Grid;

use Zoosper\AdminGrid\GridWorkspaceAuditLoggerInterface;
use Zoosper\Audit\Contract\AuditLoggerInterface;

/** Bridges the Admin Grid package contract to Zoosper's existing audit logger. */
final readonly class AdminGridAuditLoggerBridge implements GridWorkspaceAuditLoggerInterface
{
    public function __construct(private AuditLoggerInterface $audit)
    {
    }

    public function logAction(string $action, array $context = []): void
    {
        $this->audit->logAction($action, $context);
    }
}










