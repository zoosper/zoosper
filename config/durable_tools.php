<?php

declare(strict_types=1);

/**
 * Durable tool registry.
 *
 * Tools listed here are intentionally committed and should not be removed by
 * cleanup-only phases unless the owning tests/docs are updated first.
 *
 * This registry is for repository hygiene only; it is not application runtime
 * configuration.
 *
 * @return array<string, array{reason: string, outputs?: list<string>}>
 */
return [
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
];
