<?php

declare(strict_types=1);

/**
 * Canonical durable tool manifest (single source of truth).
 *
 * Both the quality gate (tools/gate.php) and the DurableToolRegistry should load
 * this file so the "which tools are durable" list can never drift between the
 * gate and the test suite.
 *
 * Keys are repo-relative tool paths. Each value carries a human reason so the
 * intent is always visible and auditable.
 *
 * @return array<string, array{reason: string}>
 */

return [
    'tools/apply-admin-form-config-aggregator-layered-loader.php' => [
        'reason' => 'Test-protected admin form config aggregator layered loader repair tool.',
    ],
    'tools/apply-admin-form-config-layered-loader.php' => [
        'reason' => 'Test-protected admin form config layered loader migration tool.',
    ],
    'tools/apply-composer-internal-package-stability.php' => [
        'reason' => 'Test-protected Composer internal package stability repair tool.',
    ],
    'tools/apply-composer-local-package-repositories.php' => [
        'reason' => 'Test-protected Composer local package repository repair tool.',
    ],
    'tools/apply-rate-limit-admin-login-policy.php' => [
        'reason' => 'Test-protected rate-limit admin login policy apply tool.',
    ],
    'tools/apply-rate-limit-admin-middleware-hook.php' => [
        'reason' => 'Test-protected rate-limit admin middleware hook apply tool.',
    ],
    'tools/apply-role-admin-latte-cutover.php' => [
        'reason' => 'Test-protected guarded RoleAdminController Latte cutover executor.',
    ],
    'tools/apply-role-admin-markup-view-cutover.php' => [
        'reason' => 'Test-protected Role Admin markup view cutover tool.',
    ],
    'tools/apply-site-lookup-service-binding.php' => [
        'reason' => 'Site lookup service binding finalisation tool retained for rollback/auditability.',
    ],
    'tools/cleanup-expired-rate-limit-buckets.php' => [
        'reason' => 'Test-protected dry-run-first expired rate-limit bucket cleanup command.',
    ],
    'tools/install-git-hooks.php' => [
        'reason' => 'Durable developer-experience tool that installs the strict quality-gate pre-push hook.',
    ],
];
