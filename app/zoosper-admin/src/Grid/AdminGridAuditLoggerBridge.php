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

    #[\Override]
    public function logAction(string $action, array $context = []): void
    {
        $actorAdminUserId = $context['admin_user_id'] ?? null;
        $gridKey = $context['grid_key'] ?? null;

        $this->audit->logAction(
            is_int($actorAdminUserId) && $actorAdminUserId > 0 ? $actorAdminUserId : null,
            null,
            $action,
            'admin_grid',
            is_string($gridKey) && $gridKey !== '' ? $gridKey : null,
            'Exported Admin Grid workspace data.',
            $context,
        );
    }
}










