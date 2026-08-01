<?php

declare(strict_types=1);

namespace Zoosper\AdminGrid;

/** Minimal host-facing audit contract required by the Admin Grid adapter. */
interface GridWorkspaceAuditLoggerInterface
{
    /** @param array<string, mixed> $context */
    public function logAction(string $action, array $context = []): void;
}
