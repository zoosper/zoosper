<?php

declare(strict_types=1);

namespace Zoosper\Core\Audit;

/**
 * Contract for recording admin audit-log actions, decoupled from any
 * concrete admin-user model or HTTP request object.
 *
 * Phase 1.41 (dependency graph correction): this interface lets feature
 * modules (page, media, two-factor, and future third-party modules) record
 * audit events without depending on zoosper/admin at all. Admin's concrete
 * AuditLogger implements this via a new, additive logAction() method — its
 * original record() method (which takes a full admin-user model object) is
 * untouched and still used by existing Admin-internal callers.
 *
 * Bound to the real AuditLogger by app/zoosper-admin/config/services.php.
 * If Admin is not installed, nothing binds this interface, and callers using
 * $services->has(AuditLoggerInterface::class) will correctly get false.
 */
interface AuditLoggerInterface
{
    /** @param array<string, mixed> $metadata */
    public function logAction(
        ?int $actorAdminUserId,
        ?string $actorEmail,
        string $action,
        string $entityType,
        ?string $entityId,
        string $summary,
        array $metadata = [],
    ): void;
}
