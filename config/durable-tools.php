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
        'reason' => 'Durable read-only RoleAdminController Latte cutover executor required by RoleAdminLatteCutoverExecutorTest.',
        'outputs' => [
            'role-admin-latte-cutover-executor.txt',
            'role-admin-latte-cutover-executor.log',
        ],
    ],
    'tools/apply-role-admin-markup-view-cutover.php' => [
        'reason' => 'Durable read-only RoleAdminController markup view cutover executor required by RoleAdminMarkupViewCutoverTest.',
        'outputs' => [
            'role-admin-markup-view-cutover.txt',
            'role-admin-markup-view-cutover.log',
        ],
    ],
    'tools/apply-site-lookup-service-binding.php' => [
        'reason' => 'Guarded service-binding patcher retained as the documented Site lookup binding finalisation tool.',
        'outputs' => [
            'site-lookup-service-binding-apply.txt',
            'site-lookup-service-binding-apply.json',
        ],
    ],
    'tools/cleanup-expired-rate-limit-buckets.php' => [
        'reason' => 'Test-protected dry-run-first expired rate-limit bucket cleanup command.',
    ],
    'tools/install-git-hooks.php' => [
        'reason' => 'Durable developer-experience tool that installs the strict quality-gate pre-push hook.',
    ],
];
