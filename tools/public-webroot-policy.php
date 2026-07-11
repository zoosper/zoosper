<?php

declare(strict_types=1);

/**
 * Shared helpers for public webroot audit/quarantine tools.
 */

function zoosper_public_policy_load(string $basePath): array
{
    $file = $basePath . '/config/public_webroot.php';

    return is_file($file) ? require $file : [];
}

function zoosper_public_relative_path(string $basePath, string $absolutePath): string
{
    $public = rtrim($basePath . '/public', '/');
    $relative = str_replace('\\', '/', substr($absolutePath, strlen($public)));

    return $relative === '' ? '/' : $relative;
}

function zoosper_public_extension(string $path): string
{
    return strtolower(pathinfo($path, PATHINFO_EXTENSION));
}

function zoosper_public_is_under_blocked_root(string $relativePath, array $blockedRoots): ?string
{
    $relativePath = '/' . ltrim(str_replace('\\', '/', $relativePath), '/');
    foreach ($blockedRoots as $root) {
        $root = '/' . trim((string) $root, '/') . '/';
        if (str_starts_with($relativePath, $root)) {
            return $root;
        }
    }

    return null;
}

function zoosper_public_is_allowed_php_file(string $relativePath, array $allowedPhpFiles): bool
{
    $relativePath = '/' . ltrim(str_replace('\\', '/', $relativePath), '/');
    foreach ($allowedPhpFiles as $allowed) {
        if ($relativePath === '/' . ltrim((string) $allowed, '/')) {
            return true;
        }
    }

    return false;
}

function zoosper_public_scan(string $basePath, array $policy): array
{
    $publicPath = $basePath . '/' . trim((string) ($policy['public_path'] ?? 'public'), '/');
    $blockedRoots = $policy['blocked_roots'] ?? [];
    $blockedExtensions = $policy['blocked_extensions'] ?? [];
    $allowedPhpFiles = $policy['allowed_php_files'] ?? ['/index.php'];
    $allowedStaticExtensions = $policy['allowed_static_extensions'] ?? [];

    $findings = [];
    if (!is_dir($publicPath)) {
        return [[
            'severity' => 'error',
            'path' => 'public',
            'reason' => 'public directory is missing',
        ]];
    }

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($publicPath, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST,
    );

    /** @var SplFileInfo $item */
    foreach ($iterator as $item) {
        $relative = zoosper_public_relative_path($basePath, $item->getPathname());
        $relative = '/' . ltrim($relative, '/');
        $blockedRoot = zoosper_public_is_under_blocked_root($relative, $blockedRoots);

        if ($blockedRoot !== null) {
            $findings[] = [
                'severity' => 'high',
                'path' => $relative,
                'reason' => 'path is under blocked public root ' . $blockedRoot,
            ];
        }

        if (!$item->isFile()) {
            continue;
        }

        $extension = zoosper_public_extension($relative);
        if ($extension !== '' && in_array($extension, $blockedExtensions, true) && !zoosper_public_is_allowed_php_file($relative, $allowedPhpFiles)) {
            $findings[] = [
                'severity' => 'critical',
                'path' => $relative,
                'reason' => 'blocked executable/server-side extension .' . $extension,
            ];
        }

        if ((str_starts_with($relative, '/static/') || str_starts_with($relative, '/assets/')) && $extension !== '' && !in_array($extension, $allowedStaticExtensions, true)) {
            $findings[] = [
                'severity' => 'medium',
                'path' => $relative,
                'reason' => 'unexpected static/assets extension .' . $extension,
            ];
        }
    }

    return $findings;
}
