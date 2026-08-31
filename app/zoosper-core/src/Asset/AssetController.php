<?php

declare(strict_types=1);

namespace Zoosper\Core\Asset;

/**
 * Single route handler that serves module assets in place.
 *
 * Framework-agnostic: serve() returns a plain structure (status, headers, and
 * either a body string or a readable file path) so it can be adapted to any
 * response object your router uses. It performs conditional-GET handling via
 * ETag / If-None-Match and sets long-lived cache headers.
 *
 * Read-only: it never writes to disk.
 *
 * Phase C2: both the cache TTL and the `immutable` Cache-Control directive are
 * now constructor-configurable (previously TTL was configurable but
 * `immutable` was hard-coded). Both default to their previous hard-coded
 * values (1 year, immutable=true), so ANY existing caller that does not pass
 * the new parameter is completely unaffected.
 */
final class AssetController
{
    public function __construct(
        private readonly AssetResolver $resolver,
        private readonly int $cacheMaxAgeSeconds = 31536000, // 1 year
        private readonly bool $cacheImmutable = true,
    ) {
    }

    /**
     * Resolve and describe the response for an asset request.
     *
     * @param array<string, string> $requestHeaders Case-insensitive-ish headers;
     *                                               'If-None-Match' is honoured.
     *
     * @return array{
     *     status: int,
     *     headers: array<string, string>,
     *     body: string,
     *     filePath: string|null
     * }
     */
    public function serve(string $module, string $path, array $requestHeaders = []): array
    {
        try {
            $asset = $this->resolver->resolve($module, $path);
        } catch (AssetNotFoundException $e) {
            return [
                'status' => 404,
                'headers' => ['Content-Type' => 'text/plain; charset=utf-8'],
                'body' => 'Asset not found.',
                'filePath' => null,
            ];
        }

        $ifNoneMatch = $this->header($requestHeaders, 'If-None-Match');
        if ($ifNoneMatch !== null && trim($ifNoneMatch) === $asset->etag) {
            return [
                'status' => 304,
                'headers' => [
                    'ETag' => $asset->etag,
                    'Cache-Control' => $this->cacheControl(),
                ],
                'body' => '',
                'filePath' => null,
            ];
        }

        return [
            'status' => 200,
            'headers' => [
                'Content-Type' => $asset->mimeType,
                'Content-Length' => (string) $asset->size,
                'ETag' => $asset->etag,
                'Last-Modified' => gmdate('D, d M Y H:i:s', $asset->lastModified) . ' GMT',
                'Cache-Control' => $this->cacheControl(),
                'X-Content-Type-Options' => 'nosniff',
            ],
            // Body provided for convenience/testing; routers may stream filePath.
            'body' => (string) file_get_contents($asset->absolutePath),
            'filePath' => $asset->absolutePath,
        ];
    }

    private function cacheControl(): string
    {
        $value = 'public, max-age=' . $this->cacheMaxAgeSeconds;

        return $this->cacheImmutable ? $value . ', immutable' : $value;
    }

    /**
     * @param array<string, string> $headers
     */
    private function header(array $headers, string $name): ?string
    {
        foreach ($headers as $key => $value) {
            if (strcasecmp((string) $key, $name) === 0) {
                return (string) $value;
            }
        }

        return null;
    }
}










