<?php

declare(strict_types=1);

/**
 * BUG FIX REGRESSION TEST — proves the corrected $env closure in
 * config/html_sanitizer.php no longer silently discards an explicitly-set
 * falsy value.
 *
 * The previous closure was:
 *   $_ENV[$key] ?? getenv($key) ?: $default
 * PHP's ?? binds tighter than ?:, so this evaluated as
 * ($_ENV[$key] ?? getenv($key)) ?: $default — an explicitly-set falsy
 * value (e.g. HTML_SANITIZER_STRIP_EMPTY=0) would be silently overridden
 * back to the default (true), the opposite of the operator's stated
 * intent.
 *
 * File placement: app/zoosper-core/tests/Unit/Html/HtmlSanitizerConfigEnvPrecedenceTest.php
 * — 5 levels up to repo root, matching other per-module tests.
 */
function htmlSanitizerConfigTestPath(): string
{
    return dirname(__DIR__, 5) . '/config/html_sanitizer.php';
}

/**
 * @param array<string, string|null> $envOverrides
 */
function htmlSanitizerConfigTestWithEnv(array $envOverrides, callable $callback): mixed
{
    $keys = array_keys($envOverrides);
    $original = [];
    foreach ($keys as $key) {
        $original[$key] = [
            'inEnv' => array_key_exists($key, $_ENV),
            'envValue' => $_ENV[$key] ?? null,
            'getenvValue' => getenv($key),
        ];
    }

    try {
        foreach ($envOverrides as $key => $value) {
            if ($value === null) {
                unset($_ENV[$key]);
                putenv($key);
            } else {
                $_ENV[$key] = $value;
                putenv($key . '=' . $value);
            }
        }

        return $callback();
    } finally {
        foreach ($keys as $key) {
            $o = $original[$key];
            if ($o['inEnv']) {
                $_ENV[$key] = $o['envValue'];
            } else {
                unset($_ENV[$key]);
            }
            if ($o['getenvValue'] !== false) {
                putenv($key . '=' . $o['getenvValue']);
            } else {
                putenv($key);
            }
        }
    }
}

it('correctly honours an explicit HTML_SANITIZER_STRIP_EMPTY=0, instead of silently reverting to the default', function (): void {
    htmlSanitizerConfigTestWithEnv(
        ['HTML_SANITIZER_STRIP_EMPTY' => '0'],
        function (): void {
            $config = require htmlSanitizerConfigTestPath();

            // Under the OLD buggy $env, this would incorrectly be `true`
            // (the default), since '0' is falsy and got treated as unset.
            expect($config['strip_empty'])->toBeFalse();
        }
    );
});

it('correctly honours an explicit HTML_SANITIZER_STRIP_EMPTY=1', function (): void {
    htmlSanitizerConfigTestWithEnv(
        ['HTML_SANITIZER_STRIP_EMPTY' => '1'],
        function (): void {
            $config = require htmlSanitizerConfigTestPath();

            expect($config['strip_empty'])->toBeTrue();
        }
    );
});

it('still applies the true default when HTML_SANITIZER_STRIP_EMPTY is genuinely not set', function (): void {
    htmlSanitizerConfigTestWithEnv(
        ['HTML_SANITIZER_STRIP_EMPTY' => null],
        function (): void {
            $config = require htmlSanitizerConfigTestPath();

            expect($config['strip_empty'])->toBeTrue();
        }
    );
});

it('defaults allow_basic_driver to false when not explicitly set', function (): void {
    htmlSanitizerConfigTestWithEnv(
        ['HTML_SANITIZER_ALLOW_BASIC_DRIVER' => null],
        function (): void {
            $config = require htmlSanitizerConfigTestPath();

            expect($config['allow_basic_driver'])->toBeFalse();
        }
    );
});

it('honours an explicit HTML_SANITIZER_ALLOW_BASIC_DRIVER=true', function (): void {
    htmlSanitizerConfigTestWithEnv(
        ['HTML_SANITIZER_ALLOW_BASIC_DRIVER' => 'true'],
        function (): void {
            $config = require htmlSanitizerConfigTestPath();

            expect($config['allow_basic_driver'])->toBeTrue();
        }
    );
});
