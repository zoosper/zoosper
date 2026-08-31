<?php

declare(strict_types=1);

use Zoosper\Core\Console\BuiltIn\GenerateSecretsCommand;
use Zoosper\Core\Console\ConsoleOutput;

function captureOutput(callable $callback): array
{
    $out = fopen('php://memory', 'w+');
    $err = fopen('php://memory', 'w+');
    $output = new ConsoleOutput($out, $err);

    $code = $callback($output);

    rewind($out);
    rewind($err);
    $stdout = (string) stream_get_contents($out);
    $stderr = (string) stream_get_contents($err);

    fclose($out);
    fclose($err);

    return [$code, $stdout, $stderr];
}

it('generates cryptographically strong application secrets to standard output', function (): void {
    $tempDir = sys_get_temp_dir() . '/zoosper-secrets-test-' . bin2hex(random_bytes(6));
    @mkdir($tempDir, 0777, true);

    try {
        $command = new GenerateSecretsCommand($tempDir);
        [$code, $output] = captureOutput(fn (ConsoleOutput $output): int => $command->run([], $output));

        expect($code)->toBe(0)
            ->and($output)->toContain('APP_KEY=base64:')
            ->and($output)->toContain('TWO_FACTOR_ENCRYPTION_KEY=')
            ->and($output)->toContain('RATE_LIMIT_IDENTITY_SALT=')
            ->and($output)->toContain('CACHE_ENCRYPTION_KEY=');
    } finally {
        if (is_file($tempDir . '/.env')) {
            @unlink($tempDir . '/.env');
        }
        @rmdir($tempDir);
    }
});

it('writes generated secrets to .env file when requested', function (): void {
    $tempDir = sys_get_temp_dir() . '/zoosper-secrets-test-' . bin2hex(random_bytes(6));
    @mkdir($tempDir, 0777, true);
    $envFile = $tempDir . '/.env';
    file_put_contents($envFile, "APP_ENV=production\nAPP_KEY=change-me\n");

    try {
        $command = new GenerateSecretsCommand($tempDir);
        [$code, $output] = captureOutput(fn (ConsoleOutput $output): int => $command->run(['--write'], $output));

        expect($code)->toBe(0);

        $contents = (string) file_get_contents($envFile);
        expect($contents)->toContain('APP_ENV=production')
            ->and($contents)->not->toContain('APP_KEY=change-me')
            ->and($contents)->toContain('APP_KEY=base64:')
            ->and($contents)->toContain('TWO_FACTOR_ENCRYPTION_KEY=')
            ->and($contents)->toContain('RATE_LIMIT_IDENTITY_SALT=')
            ->and($contents)->toContain('CACHE_ENCRYPTION_KEY=');
    } finally {
        @unlink($envFile);
        @rmdir($tempDir);
    }
});

it('audits existing secrets and fails when placeholders are present', function (): void {
    $tempDir = sys_get_temp_dir() . '/zoosper-secrets-test-' . bin2hex(random_bytes(6));
    @mkdir($tempDir, 0777, true);
    $envFile = $tempDir . '/.env';
    file_put_contents($envFile, "APP_KEY=change-me\nTWO_FACTOR_ENCRYPTION_KEY=\nRATE_LIMIT_IDENTITY_SALT=secure-salt-12345678901234567890\nCACHE_ENCRYPTION_KEY=secure-key-12345678901234567890\n");

    try {
        $command = new GenerateSecretsCommand($tempDir);
        [$code, $output] = captureOutput(fn (ConsoleOutput $output): int => $command->run(['--check'], $output));

        expect($code)->toBe(1)
            ->and($output)->toContain("[FAIL] APP_KEY: Uses insecure placeholder value ('change-me')")
            ->and($output)->toContain('[FAIL] TWO_FACTOR_ENCRYPTION_KEY: Missing or empty')
            ->and($output)->toContain('[OK]   RATE_LIMIT_IDENTITY_SALT: Present and secure')
            ->and($output)->toContain('[OK]   CACHE_ENCRYPTION_KEY: Present and secure');
    } finally {
        @unlink($envFile);
        @rmdir($tempDir);
    }
});

it('audits existing secrets and passes when all are strong', function (): void {
    $tempDir = sys_get_temp_dir() . '/zoosper-secrets-test-' . bin2hex(random_bytes(6));
    @mkdir($tempDir, 0777, true);
    $envFile = $tempDir . '/.env';
    file_put_contents($envFile, "APP_KEY=base64:AbCdEf1234567890AbCdEf1234567890AbCdEf12345=\nTWO_FACTOR_ENCRYPTION_KEY=" . bin2hex(random_bytes(32)) . "\nRATE_LIMIT_IDENTITY_SALT=" . bin2hex(random_bytes(32)) . "\nCACHE_ENCRYPTION_KEY=" . bin2hex(random_bytes(32)) . "\n");

    try {
        $command = new GenerateSecretsCommand($tempDir);
        [$code, $output] = captureOutput(fn (ConsoleOutput $output): int => $command->run(['--check'], $output));

        expect($code)->toBe(0)
            ->and($output)->toContain('All security secrets passed verification.');
    } finally {
        @unlink($envFile);
        @rmdir($tempDir);
    }
});










