<?php

declare(strict_types=1);

/** @return array{code:int,output:string} */
function runFreshInstallCommand(string $root, string $database, array $arguments): array
{
    $environment = $_ENV;
    $command = array_merge([PHP_BINARY, $root . '/bin/zoosper'], $arguments);
    $process = proc_open($command, [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes, $root, $environment);
    if (!is_resource($process)) {
        throw new RuntimeException('Unable to start Zoosper CLI process.');
    }
    $output = stream_get_contents($pipes[1]) . stream_get_contents($pipes[2]);
    fclose($pipes[1]); fclose($pipes[2]);
    return ['code' => proc_close($process), 'output' => $output];
}

it('proves a disposable alpha install from zero through bootstrap and idempotent migration', function (): void {
    $root = dirname(__DIR__, 5);
    $database = tempnam(sys_get_temp_dir(), 'zoosper-alpha-');
    expect($database)->not->toBeFalse();

    $environmentPath = $root . '/.env';
    $environmentExisted = is_file($environmentPath);
    $originalEnvironment = $environmentExisted ? file_get_contents($environmentPath) : null;
    $lock = fopen($root . '/var/alpha-install-smoke.lock', 'c+');
    if (!is_resource($lock) || !flock($lock, LOCK_EX)) {
        throw new RuntimeException('Unable to lock the alpha fresh-install environment.');
    }
    file_put_contents($environmentPath, implode("\n", [
        'APP_ENV=testing',
        'APP_DEBUG=false',
        'DB_CONNECTION=sqlite',
        'DB_DATABASE=' . $database,
        'TWO_FACTOR_ENCRYPTION_KEY=' . base64_encode(random_bytes(32)),
        '',
    ]));

    try {
        $first = runFreshInstallCommand($root, $database, ['migrate']);
        expect($first['code'])->toBe(0, $first['output']);

        $second = runFreshInstallCommand($root, $database, ['migrate']);
        expect($second['code'])->toBe(0, $second['output']);

        $admin = runFreshInstallCommand($root, $database, [
            'admin:create', '--email=alpha-smoke@example.test', '--password=Alpha-Smoke-834!', '--name=Alpha Smoke',
        ]);
        expect($admin['code'])->toBe(0, $admin['output'])->and($admin['output'])->toContain('Created super admin user');

        $site = runFreshInstallCommand($root, $database, [
            'site:create', '--code=alpha', '--name=Alpha Site', '--host=alpha.example.test', '--homepage=home',
        ]);
        expect($site['code'])->toBe(0, $site['output'])->and($site['output'])->toContain('Created site');

        $duplicate = runFreshInstallCommand($root, $database, [
            'admin:create', '--email=alpha-smoke@example.test', '--password=Alpha-Smoke-834!', '--name=Alpha Smoke',
        ]);
        expect($duplicate['code'])->toBe(1)->and($duplicate['output'])->toContain('already exists');

        $pdo = new \PDO('sqlite:' . $database, null, null, [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]);
        $tables = $pdo->query("SELECT name FROM sqlite_master WHERE type='table'")->fetchAll(\PDO::FETCH_COLUMN);
        expect($tables)->toContain('migrations', 'admin_users', 'admin_roles', 'admin_permissions', 'sites', 'site_domains', 'pages');
        $revisionColumns = $pdo->query('PRAGMA table_info(page_revisions)')->fetchAll(\PDO::FETCH_ASSOC);
        expect(array_column($revisionColumns, 'name'))->toContain('slug', 'status', 'content_format', 'content_json', 'meta_title', 'canonical_url');
        $user = $pdo->query("SELECT email, password_hash FROM admin_users WHERE email='alpha-smoke@example.test'")->fetch(\PDO::FETCH_ASSOC);
        expect($user['email'])->toBe('alpha-smoke@example.test')
            ->and($user['password_hash'])->not->toBe('Alpha-Smoke-834!')
            ->and(password_verify('Alpha-Smoke-834!', $user['password_hash']))->toBeTrue();
        expect((int) $pdo->query("SELECT COUNT(*) FROM sites WHERE code='alpha'")->fetchColumn())->toBe(1)
            ->and((int) $pdo->query("SELECT COUNT(*) FROM site_domains WHERE host='alpha.example.test'")->fetchColumn())->toBe(1);
    } finally {
        if ($environmentExisted) {
            file_put_contents($environmentPath, (string) $originalEnvironment);
        } else {
            @unlink($environmentPath);
        }
        flock($lock, LOCK_UN);
        fclose($lock);
        @unlink($root . '/var/alpha-install-smoke.lock');
        @unlink((string) $database);
    }
});
