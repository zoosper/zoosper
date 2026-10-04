<?php

declare(strict_types=1);

use Zoosper\Core\Security\SecurityHeaders;

function adminCspRepositoryRoot(): string
{
    return dirname(__DIR__, 5);
}

require_once adminCspRepositoryRoot() . '/bootstrap/autoload.php';

it('keeps the default CSP enforcing without unsafe eval or external origins', function (): void {
    $root = adminCspRepositoryRoot();
    $config = require $root . '/config/security.php';
    $csp = $config['csp'];
    $headers = (new SecurityHeaders([], $csp))->resolvedHeaders();

    expect($csp['enabled'])->toBeTrue()
        ->and($csp['report_only'])->toBeFalse()
        ->and($headers)->toHaveKey('Content-Security-Policy')
        ->and($headers)->not->toHaveKey('Content-Security-Policy-Report-Only')
        ->and($csp['policy'])->toContain("script-src 'self'")
        ->and($csp['policy'])->not->toContain("'unsafe-eval'")
        ->and($csp['policy'])->not->toMatch('/https?:\\/\\//');
});

it('keeps only the reviewed style and data url exceptions', function (): void {
    $root = adminCspRepositoryRoot();
    $config = require $root . '/config/security.php';
    $policy = (string) $config['csp']['policy'];

    expect($policy)->toContain("style-src 'self' 'unsafe-inline'")
        ->and($policy)->toContain("img-src 'self' data:")
        ->and($policy)->toContain("font-src 'self' data:")
        ->and($policy)->not->toContain('blob:');
});

it('rejects unreviewed inline executable markup in production Admin sources', function (): void {
    $root = adminCspRepositoryRoot();
    $roots = [$root . '/app', $root . '/packages', $root . '/themes/admin'];
    $allowedJsonScripts = [
        'app/zoosper-settings/resources/views/admin/settings/index.php',
        'packages/zoosper-admin-grid/src/GridBulkActionManifestRenderer.php',
    ];
    $violations = [];

    foreach ($roots as $scanRoot) {
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($scanRoot, FilesystemIterator::SKIP_DOTS));
        foreach ($iterator as $file) {
            if (!$file->isFile()) {
                continue;
            }
            $path = $file->getPathname();
            $relative = str_replace($root . '/', '', $path);
            if (str_contains($relative, '/tests/') || !preg_match('/\\.(php|latte)$/', $relative)) {
                continue;
            }
            $source = (string) file_get_contents($path);
            $sourceForMarkup = preg_replace(
                '/str_contains\(\s*\$[A-Za-z_][A-Za-z0-9_]*\s*,\s*([\x22\x27])<script\1\s*\)/i',
                'str_contains($value, $needle)',
                $source,
            ) ?? $source;
            if (preg_match("~<script\\b(?![^>]*\\bsrc=)(?![^>]*\\btype=[\\x22\\x27]application/json[\\x22\\x27])~i", $sourceForMarkup) === 1) {
                $violations[] = $relative . ': inline executable script';
            }
            if (preg_match('/\\son[a-z]+\\s*=/i', $sourceForMarkup) === 1) {
                $violations[] = $relative . ': inline event handler';
            }
            if (preg_match("~<script\\b[^>]*\\btype=[\\x22\\x27]application/json[\\x22\\x27]~i", $sourceForMarkup) === 1
                && !in_array($relative, $allowedJsonScripts, true)) {
                $violations[] = $relative . ': unreviewed JSON data script';
            }
        }
    }

    expect($violations)->toBe([]);
});

it('keeps inline style elements limited to reviewed Admin and Editor exceptions', function (): void {
    $root = adminCspRepositoryRoot();
    $allowed = [
        'app/zoosper-admin/src/Controller/LoginController.php',
        'app/zoosper-admin/src/Controller/PasswordResetController.php',
        'app/zoosper-two-factor/src/Controller/AdminTwoFactorChallengeController.php',
        'app/zoosper-auth/src/Http/CsrfMiddleware.php',
    ];
    $found = [];

    foreach ([$root . '/app', $root . '/packages', $root . '/themes/admin'] as $scanRoot) {
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($scanRoot, FilesystemIterator::SKIP_DOTS));
        foreach ($iterator as $file) {
            if (!$file->isFile()) {
                continue;
            }
            $relative = str_replace($root . '/', '', $file->getPathname());
            if (str_contains($relative, '/tests/') || !preg_match('/\\.(php|latte)$/', $relative)) {
                continue;
            }
            if (stripos((string) file_get_contents($file->getPathname()), '<style>') !== false) {
                $found[] = $relative;
            }
        }
    }

    sort($found);
    sort($allowed);
    expect($found)->toBe($allowed)
        ->and((string) file_get_contents($root . '/public/assets/admin/js/editorjs.bundle.js'))
        ->toContain('document.createElement("style")')
        ->toContain('readAsDataURL')
        ->not->toContain('eval(');
});
