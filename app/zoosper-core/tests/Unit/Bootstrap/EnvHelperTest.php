<?php

declare(strict_types=1);

/**
 * BUG FIX REGRESSION TEST — proves bootstrap/autoload.php's env() helper
 * and .env parser fixes, using a REAL, isolated PHP subprocess with a
 * controlled temporary .env file and a minimal stub vendor/autoload.php.
 *
 * WHY A SUBPROCESS: bootstrap/autoload.php defines env() as a genuinely
 * global function using a `static $loaded` flag internal to the function
 * itself. By the time ANY Pest test in this suite runs, the REAL
 * bootstrap/autoload.php has almost certainly already been required at
 * least once elsewhere in the process (directly, or via
 * zoosperEnsureRuntimeHelpers() in FrontendBootTest), meaning
 * function_exists('env') is already true and the static $loaded flag is
 * already latched — there is no way to re-test "first load" behaviour
 * within the SAME PHP process. Spawning a fresh `php -r` subprocess, with
 * its own completely separate process memory, is the only way to
 * genuinely exercise this file's parsing logic from a clean slate — this
 * mirrors the same exec()-based subprocess testing pattern already used
 * elsewhere in this codebase for standalone script verification.
 *
 * Each test case's expected value was independently verified (by
 * simulating the exact same parsing logic faithfully) BEFORE this test
 * was written, so the assertions below reflect actually-correct behaviour,
 * not a guess.
 *
 * File placement: app/zoosper-core/tests/Unit/Bootstrap/EnvHelperTest.php
 * — 5 levels up to repo root, matching other per-module tests.
 */
function envHelperTestScaffold(string $envFileContents): array
{
    $tmp = sys_get_temp_dir() . '/zoosper-env-helper-test-' . bin2hex(random_bytes(6));
    mkdir($tmp . '/bootstrap', 0775, true);
    mkdir($tmp . '/vendor', 0775, true);

    // Minimal Composer autoloader stub — just enough for
    // bootstrap/autoload.php's own `require $composerAutoload;` to
    // succeed without pulling in the entire real dependency graph, which
    // is irrelevant to what this test verifies (the env() parsing logic).
    file_put_contents($tmp . '/vendor/autoload.php', "<?php\n// stub for test isolation\n");

    // Copy the REAL bootstrap/autoload.php (the actual file under test),
    // not a re-typed duplicate, so this test genuinely exercises the real
    // fix rather than a hand-copied approximation of it.
    $realBootstrapPath = dirname(__DIR__, 5) . '/bootstrap/autoload.php';
    copy($realBootstrapPath, $tmp . '/bootstrap/autoload.php');

    file_put_contents($tmp . '/.env', $envFileContents);

    return [$tmp, $tmp . '/bootstrap/autoload.php'];
}

function envHelperTestRun(string $envFileContents, string $key, string $default = ''): string
{
    [$tmp, $bootstrapPath] = envHelperTestScaffold($envFileContents);

    $script = sprintf(
        'require %s; echo var_export(env(%s, %s), true);',
        var_export($bootstrapPath, true),
        var_export($key, true),
        var_export($default, true),
    );

    $command = escapeshellarg(PHP_BINARY) . ' -r ' . escapeshellarg($script) . ' 2>&1';
    exec($command, $outputLines, $exitCode);

    exec('rm -rf ' . escapeshellarg($tmp));

    return trim(implode("\n", $outputLines));
}

it('correctly parses an explicit falsy value instead of silently reverting to the default (the core precedence-bug fix)', function (): void {
    $result = envHelperTestRun("STRIP_EMPTY=0\n", 'STRIP_EMPTY', 'true');

    // Under the OLD buggy env(), this would incorrectly return 'true'
    // (the default), since '0' is falsy and ?: treated it as unset.
    expect($result)->toBe("'0'");
});

it('strips a genuinely matched pair of quotes from a value', function (): void {
    $result = envHelperTestRun("APP_KEY=\"my-real-secret-value\"\n", 'APP_KEY');

    expect($result)->toBe("'my-real-secret-value'");
});

it('does NOT strip an unmatched trailing quote character from an otherwise-unquoted value', function (): void {
    // A password that legitimately ends in a single quote character, with
    // no matching opening quote — must be preserved exactly.
    $result = envHelperTestRun("DB_PASS=abc'\n", 'DB_PASS');

    expect($result)->toBe("'abc\''");
});

it('strips a trailing inline comment from an unquoted value', function (): void {
    $result = envHelperTestRun("DB_PASS=secret # prod note\n", 'DB_PASS');

    expect($result)->toBe("'secret'");
});

it('preserves a literal # inside a genuinely quoted value (does not treat it as a comment)', function (): void {
    $result = envHelperTestRun("SOME_VALUE='value#withhash'\n", 'SOME_VALUE');

    expect($result)->toBe("'value#withhash'");
});

it('supports a leading export keyword', function (): void {
    $result = envHelperTestRun("export APP_KEY=exported-value\n", 'APP_KEY');

    expect($result)->toBe("'exported-value'");
});

it('falls back to the provided default when the key is genuinely not present', function (): void {
    $result = envHelperTestRun("SOME_OTHER_KEY=value\n", 'MISSING_KEY', 'the-default');

    expect($result)->toBe("'the-default'");
});

it('ignores comment lines and blank lines correctly (no regression)', function (): void {
    $result = envHelperTestRun("# this is a comment\n\nAPP_KEY=real-value\n", 'APP_KEY');

    expect($result)->toBe("'real-value'");
});










