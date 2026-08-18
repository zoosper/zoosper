<?php
declare(strict_types=1);
use Zoosper\Logger\Manager\LogManager;
test('writes through the real Marko file logger with daily rotation and a legacy link', function (): void {
    $root = sys_get_temp_dir() . '/zoosper-logger-' . bin2hex(random_bytes(6));
    $config = new class { public function get(string $key, mixed $default = null): mixed { return ['logging.path' => 'var/log', 'logging.default_file' => 'system.log'][$key] ?? $default; } };
    $logger = (new LogManager($config, $root))->default();
    $logger->info('phase-10ax-real-marko', ['token' => 'must-not-leak']);
    $dated = $root . '/var/log/system-' . date('Y-m-d') . '.log';
    expect($dated)->toBeFile()->and(file_get_contents($dated))->toContain('phase-10ax-real-marko')->not->toContain('must-not-leak')->and($root . '/var/log/system.log')->toBeFile();
    exec('rm -rf ' . escapeshellarg($root));
});
