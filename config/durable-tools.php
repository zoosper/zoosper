<?php

declare(strict_types=1);

/**
 * Canonical durable tool manifest (single source of truth).
 *
 * Both the quality gate (tools/gate.php) and the durable-tool audit/registry
 * tooling load THIS file, so the "which tools are durable" list can never drift
 * between the gate and the test suite. (Phase 1.98 consolidated the former
 * config/durable_tools.php into this file.)
 *
 * Keys are repo-relative tool paths. Each value carries a human reason so the
 * intent is always visible and auditable. Some entries also declare the report
 * artefacts they emit via an optional `outputs` list.
 *
 * @return array<string, array{reason: string, outputs?: list<string>}>
 */

return [
    'tools/cleanup-expired-rate-limit-buckets.php' => [
        'reason' => 'Test-protected dry-run-first expired rate-limit bucket cleanup command.',
    ],
    'tools/install-git-hooks.php' => [
        'reason' => 'Durable developer-experience tool that installs the strict quality-gate pre-push hook.',
    ],
];








