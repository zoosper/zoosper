<?php

declare(strict_types=1);

namespace Zoosper\Core\Asset;

/**
 * Resolves a logical (module, relativePath) pair to a concrete, safe file on
 * disk. This is the security core of the asset pipeline:
 *
 *  - path traversal (../, absolute paths, null bytes) is rejected;
 *  - the resolved real path MUST remain inside the module's assets base dir;
 *  - only allowlisted file extensions are served, each mapped to a MIME type.
 *
 * It only reads metadata here; the controller streams the bytes.
 */
final class AssetResolver
{
    /**
     * Allowlisted extensions and their MIME types. Anything not listed is denied.
     *
     * @var array<string, string>
     */
    private const MIME_TYPES = [
        'css' => 'text/css',
        'js' => 'text/javascript',
        'mjs' => 'text/javascript',
        'map' => 'application/json',
        'json' => 'application/json',
        'svg' => 'image/svg+xml',
        'png' => 'image/png',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'gif' => 'image/gif',
        'webp' => 'image/webp',
        'ico' => 'image/x-icon',
        'woff' => 'font/woff',
        'woff2' => 'font/woff2',
        'ttf' => 'font/ttf',
        'eot' => 'application/vnd.ms-fontobject',
        'txt' => 'text/plain',
    ];

    public function __construct(
        private readonly AssetModuleRegistry $registry,
    ) {
    }

    /**
     * Resolve an asset, or throw when it is unknown/unsafe/unsupported.
     *
     * @throws AssetNotFoundException
     */
    public function resolve(string $module, string $relativePath): ResolvedAsset
    {
        $baseDir = $this->registry->baseDir($module);
        if ($baseDir === null) {
            throw new AssetNotFoundException("Unknown asset module: {$module}");
        }

        if (str_contains($relativePath, "\0")) {
            throw new AssetNotFoundException('Illegal null byte in asset path.');
        }

        // Normalise separators and strip any leading slashes.
        $relative = ltrim(str_replace('\\', '/', $relativePath), '/');

        // Reject obvious traversal attempts up-front.
        if ($relative === '' || str_contains($relative, '../') || str_contains($relative, '/..')) {
            throw new AssetNotFoundException('Illegal asset path.');
        }

        $extension = strtolower(pathinfo($relative, PATHINFO_EXTENSION));
        if (!isset(self::MIME_TYPES[$extension])) {
            throw new AssetNotFoundException("Unsupported asset type: .{$extension}");
        }

        $candidate = $baseDir . '/' . $relative;
        $realBase = realpath($baseDir);
        $realFile = realpath($candidate);

        if ($realBase === false || $realFile === false || !is_file($realFile)) {
            throw new AssetNotFoundException('Asset not found.');
        }

        // Containment check: the resolved file must live under the module base.
        $baseWithSep = rtrim($realBase, '/') . '/';
        if (!str_starts_with($realFile, $baseWithSep)) {
            throw new AssetNotFoundException('Asset path escapes module boundary.');
        }

        $size = (int) filesize($realFile);
        $mtime = (int) filemtime($realFile);
        $etag = '"' . substr(hash('sha256', $realFile . '|' . $size . '|' . $mtime), 0, 32) . '"';

        return new ResolvedAsset(
            absolutePath: $realFile,
            mimeType: self::MIME_TYPES[$extension],
            size: $size,
            etag: $etag,
            lastModified: $mtime,
        );
    }

    /**
     * @return array<string, string>
     */
    public static function supportedTypes(): array
    {
        return self::MIME_TYPES;
    }
}
