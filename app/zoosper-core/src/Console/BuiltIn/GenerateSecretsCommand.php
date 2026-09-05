<?php

declare(strict_types=1);

namespace Zoosper\Core\Console\BuiltIn;

use Zoosper\Core\Console\ConsoleCommandInterface;
use Zoosper\Core\Console\ConsoleOptions;
use Zoosper\Core\Console\ConsoleOutput;

/**
 * Generates cryptographically secure secrets and keys for application security,
 * two-factor authentication, salted rate-limiting, and cache encryption.
 */
final readonly class GenerateSecretsCommand implements ConsoleCommandInterface
{
    /** @var list<string> */
    private const EXACT_PLACEHOLDERS = [
        'change-me',
        'change-me-before-production',
        'secret',
        'changeme',
        'placeholder',
        'default',
        'password',
        'test',
        'null',
    ];

    public function __construct(private string $basePath)
    {
    }

    public function name(): string
    {
        return 'security:generate-secrets';
    }

    public function description(): string
    {
        return 'Generate or audit cryptographically strong application secrets and encryption keys.';
    }

    public function run(array $args, ConsoleOutput $output): int
    {
        $options = ConsoleOptions::parse($args);

        if (in_array('--check', $args, true) || isset($options['check'])) {
            return $this->runCheck($output, $args, $options);
        }

        return $this->runGenerate($output, $args, $options);
    }

    /**
     * @param list<string> $args
     * @param array<string, string> $options
     */
    private function runGenerate(ConsoleOutput $output, array $args, array $options): int
    {
        $generated = [
            'APP_KEY' => 'base64:' . base64_encode(random_bytes(32)),
            'TWO_FACTOR_ENCRYPTION_KEY' => bin2hex(random_bytes(32)),
            'RATE_LIMIT_IDENTITY_SALT' => bin2hex(random_bytes(32)),
            'CACHE_ENCRYPTION_KEY' => bin2hex(random_bytes(32)),
        ];

        $envFile = $options['env-file'] ?? ($this->basePath . '/.env');
        $write = in_array('--write', $args, true) || isset($options['write']) || in_array('--update', $args, true) || isset($options['update']);
        $force = in_array('--force', $args, true) || isset($options['force']);

        if ($write) {
            $updated = $this->writeToEnvFile($envFile, $generated, $force);
            $output->writeln('Secrets successfully written to ' . $envFile);
            foreach ($updated as $key => $status) {
                $output->writeln("  - {$key}: {$status}");
            }
            return 0;
        }

        $output->writeln('Generated Cryptographically Strong Secrets:');
        $output->writeln('');
        foreach ($generated as $key => $value) {
            $output->writeln("{$key}={$value}");
        }
        $output->writeln('');
        $output->writeln('Pass --write to update your .env file directly, or copy the values above.');

        return 0;
    }

    /**
     * @param list<string> $args
     * @param array<string, string> $options
     */
    private function runCheck(ConsoleOutput $output, array $args, array $options): int
    {
        $envFile = $options['env-file'] ?? ($this->basePath . '/.env');
        $fileValues = is_file($envFile) ? $this->parseEnvFile($envFile) : [];

        $keys = [
            'APP_KEY',
            'TWO_FACTOR_ENCRYPTION_KEY',
            'RATE_LIMIT_IDENTITY_SALT',
            'CACHE_ENCRYPTION_KEY',
        ];

        $hasFailure = false;
        $output->writeln('Auditing Application Security Secrets:');

        foreach ($keys as $key) {
            $value = is_file($envFile)
                ? ($fileValues[$key] ?? '')
                : (getenv($key) !== false ? (string) getenv($key) : ($_ENV[$key] ?? ''));
            $trimmed = trim((string) $value);

            if ($trimmed === '') {
                $output->writeln("  [FAIL] {$key}: Missing or empty");
                $hasFailure = true;
                continue;
            }

            if ($this->isPlaceholder($trimmed)) {
                $output->writeln("  [FAIL] {$key}: Uses insecure placeholder value ('{$trimmed}')");
                $hasFailure = true;
                continue;
            }

            $output->writeln("  [OK]   {$key}: Present and secure");
        }

        if ($hasFailure) {
            $output->writeln('');
            $output->writeln('One or more secrets failed security audit. Run `php bin/zoosper security:generate-secrets --write` to remediate.');
            return 1;
        }

        $output->writeln('');
        $output->writeln('All security secrets passed verification.');
        return 0;
    }

    private function isPlaceholder(string $value): bool
    {
        $lower = strtolower($value);
        if (str_starts_with($lower, 'base64:')) {
            $decoded = base64_decode(substr($value, 7), true);
            if ($decoded !== false) {
                $lowerDecoded = strtolower(trim($decoded));
                if (in_array($lowerDecoded, self::EXACT_PLACEHOLDERS, true) || str_contains($lowerDecoded, 'change-me')) {
                    return true;
                }
            }
        }

        if (in_array($lower, self::EXACT_PLACEHOLDERS, true)) {
            return true;
        }

        return str_contains($lower, 'change-me') || str_contains($lower, 'placeholder');
    }

    /**
     * @param array<string, string> $generated
     * @return array<string, string>
     */
    private function writeToEnvFile(string $envFile, array $generated, bool $force): array
    {
        $lines = is_file($envFile) ? (explode("\n", (string) file_get_contents($envFile)) ?: []) : [];
        $existing = [];
        $keyLineIndex = [];

        foreach ($lines as $index => $line) {
            $trimmed = trim($line);
            if ($trimmed === '' || str_starts_with($trimmed, '#') || !str_contains($trimmed, '=')) {
                continue;
            }
            [$key, $val] = explode('=', $trimmed, 2);
            $key = trim($key);
            $existing[$key] = trim($val);
            $keyLineIndex[$key] = $index;
        }

        $statusReport = [];

        foreach ($generated as $key => $newValue) {
            $currentValue = $existing[$key] ?? null;
            $shouldReplace = $currentValue === null
                || $currentValue === ''
                || $this->isPlaceholder($currentValue)
                || $force;

            if (!$shouldReplace) {
                $statusReport[$key] = 'Preserved existing value';
                continue;
            }

            if (isset($keyLineIndex[$key])) {
                $lines[$keyLineIndex[$key]] = "{$key}={$newValue}";
                $statusReport[$key] = 'Updated existing line';
            } else {
                $lines[] = "{$key}={$newValue}";
                $statusReport[$key] = 'Appended new line';
            }
        }

        file_put_contents($envFile, implode("\n", $lines));
        @chmod($envFile, 0600);

        return $statusReport;
    }

    /**
     * @return array<string, string>
     */
    private function parseEnvFile(string $envFile): array
    {
        $values = [];
        $lines = explode("\n", (string) file_get_contents($envFile));
        foreach ($lines as $line) {
            $trimmed = trim($line);
            if ($trimmed === '' || str_starts_with($trimmed, '#') || !str_contains($trimmed, '=')) {
                continue;
            }
            [$k, $v] = explode('=', $trimmed, 2);
            $values[trim($k)] = trim($v);
        }

        return $values;
    }
}










