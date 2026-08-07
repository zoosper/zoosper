<?php

declare(strict_types=1);

it('publishes the environment-owned session timeout as read-only security metadata', function (): void {
    $root = dirname(__DIR__, 5);
    $config = require $root . '/app/zoosper-settings/config/admin_settings.php';
    $groups = $config[0]['groups'];
    $security = array_values(array_filter($groups, static fn (array $group): bool => $group['id'] === 'security'));

    expect($security)->toHaveCount(1)
        ->and($security[0]['settings'])->toHaveCount(5)
        ->and($security[0]['settings'][0]['path'])->toBe('admin.session_idle_timeout')
        ->and($security[0]['settings'][0]['default'])->toBe(7200)
        ->and($security[0]['settings'][0]['read_only'])->toBeTrue()
        ->and($security[0]['settings'][1]['path'])->toBe('session.lifetime_seconds')
        ->and($security[0]['settings'][1]['read_only'])->toBeTrue()
        ->and($security[0]['settings'][2]['path'])->toBe('session.samesite')
        ->and($security[0]['settings'][2]['read_only'])->toBeTrue()
        ->and($security[0]['settings'][3]['path'])->toBe('security.trusted_proxies')
        ->and($security[0]['settings'][3]['read_only'])->toBeTrue()
        ->and($security[0]['settings'][4]['path'])->toBe('rate_limit.mode')
        ->and($security[0]['settings'][4]['read_only'])->toBeTrue();
});
