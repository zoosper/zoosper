<?php

declare(strict_types=1);

/** @return array{exit: int, output: string} */
function runEnvironmentPrecedenceProbe(string $prefix): array
{
    $root = dirname(__DIR__, 5);
    $temporary = sys_get_temp_dir() . '/zoosper-env-precedence-' . bin2hex(random_bytes(6));
    mkdir($temporary . '/bootstrap', 0775, true);
    mkdir($temporary . '/vendor', 0775, true);
    copy($root . '/bootstrap/autoload.php', $temporary . '/bootstrap/autoload.php');
    file_put_contents($temporary . '/vendor/autoload.php', "<?php\n");
    file_put_contents($temporary . '/.env', "APP_ENV=local\nSESSION_SECURE=false\n");

    $script = sprintf(
        '%s require %s; echo env("APP_ENV") . "|" . env("SESSION_SECURE");',
        $prefix,
        var_export($temporary . '/bootstrap/autoload.php', true),
    );

    $process = proc_open(
        [PHP_BINARY, '-r', $script],
        [
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ],
        $pipes,
    );

    $output = '';
    if (is_resource($process)) {
        $stdout = stream_get_contents($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        $exitCode = proc_close($process);
        $output = trim($stdout . ($stderr ? "\n" . $stderr : ''));
    } else {
        $exitCode = 1;
    }

    @unlink($temporary . '/bootstrap/autoload.php');
    @unlink($temporary . '/vendor/autoload.php');
    @unlink($temporary . '/.env');
    @rmdir($temporary . '/bootstrap');
    @rmdir($temporary . '/vendor');
    @rmdir($temporary);

    return ['exit' => $exitCode, 'output' => $output];
}

it('keeps process-manager values authoritative over .env', function (): void {
    $result = runEnvironmentPrecedenceProbe(
        'putenv("APP_ENV=production"); putenv("SESSION_SECURE=true");'
    );

    expect($result['exit'])->toBe(0)
        ->and($result['output'])->toBe('production|true');
});

it('keeps existing $_ENV values authoritative when process values are absent', function (): void {
    $result = runEnvironmentPrecedenceProbe(
        'putenv("APP_ENV"); putenv("SESSION_SECURE"); '
        . '$_ENV["APP_ENV"]="staging"; $_ENV["SESSION_SECURE"]="true";'
    );

    expect($result['exit'])->toBe(0)
        ->and($result['output'])->toBe('staging|true');
});
