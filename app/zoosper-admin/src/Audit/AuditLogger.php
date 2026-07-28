<?php

declare(strict_types=1);

namespace Zoosper\Admin\Audit;

use Zoosper\Auth\Model\AdminUser;
use Zoosper\Core\Audit\AuditLoggerInterface;
use Zoosper\Core\Http\Request;

/**
 * Phase 1.41: now implements AuditLoggerInterface via the new, additive
 * logAction() method below. The original record() method (used by existing
 * Admin-internal callers with a full AdminUser) is completely unchanged.
 */
final readonly class AuditLogger implements AuditLoggerInterface
{
    public function __construct(private AuditLogRepository $logs)
    {
    }

    /** @param array<string, mixed> $metadata */
    public function record(?AdminUser $actor, string $action, string $entityType, ?string $entityId, string $summary, array $metadata = [], ?Request $request = null): void
    {
        $this->logs->record(
            adminUserId: $actor?->id,
            actorEmail: $actor?->email,
            action: $action,
            entityType: $entityType,
            entityId: $entityId,
            summary: $summary,
            metadata: $metadata,
            ipAddress: $request?->clientIp(),
            userAgent: $request?->userAgent(),
        );
    }

    /**
     * AuditLoggerInterface implementation (Phase 1.41). Accepts a plain
     * actor id/email pair instead of a full AdminUser, so feature modules
     * that only have an id available (e.g. AdminTwoFactorResetService) can
     * log audit events without needing to look up or depend on AdminUser.
     *
     * @param array<string, mixed> $metadata
     */
    public function logAction(
        ?int $actorAdminUserId,
        ?string $actorEmail,
        string $action,
        string $entityType,
        ?string $entityId,
        string $summary,
        array $metadata = [],
    ): void {
        $this->logs->record(
            adminUserId: $actorAdminUserId,
            actorEmail: $actorEmail,
            action: $action,
            entityType: $entityType,
            entityId: $entityId,
            summary: $summary,
            metadata: $metadata,
            ipAddress: null,
            userAgent: null,
        );
    }
}
