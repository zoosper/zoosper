<?php

declare(strict_types=1);

namespace Zoosper\Core\Url;

use InvalidArgumentException;
use Zoosper\Core\Site\SiteContext;

/**
 * Resolves public URLs through separate dynamic, media and static CDN channels.
 *
 * Dynamic URLs can now be driven by a resolved SiteContext, so application code
 * does not need to hard-code store codes. Media URLs are intended for uploaded
 * assets such as images/videos. Static URLs are intended for CSS, JavaScript,
 * JSON and immutable theme/module assets. This service must never receive or
 * embed credentials, OTPs, TOTP secrets, recovery-code plaintext, reset tokens,
 * payment data or customer-private values in URL paths.
 */
final readonly class CdnUrlResolver
{
    /**
     * @param array<string, mixed> $config
     */
    public function __construct(private array $config)
    {
    }

    /**
     * Resolve a store-view aware dynamic URL by optional legacy store-view code.
     *
     * New request-aware consumers should prefer dynamicForContext() so store view
     * resolution comes from SiteContext rather than hard-coded store codes.
     */
    public function dynamic(string $path = '/', ?string $storeCode = null): string
    {
        $baseUrl = $this->dynamicBaseUrl($storeCode);

        return $this->join($baseUrl, $path);
    }

    /**
     * Resolve a dynamic URL from the current site/store/store-view context.
     */
    public function dynamicForContext(string $path, SiteContext $context): string
    {
        $baseUrl = $context->baseUrl !== '' ? $context->baseUrl : $this->dynamicBaseUrl($context->storeViewCode);

        return $this->join($baseUrl, $path);
    }

    /**
     * Resolve an uploaded media asset URL.
     */
    public function media(string $path): string
    {
        return $this->assetUrl(CdnUrlType::MEDIA, $path);
    }

    /**
     * Resolve a static asset URL for CSS, JS, JSON or theme/module assets.
     */
    public function staticAsset(string $path): string
    {
        return $this->assetUrl(CdnUrlType::STATIC, $path);
    }

    /**
     * Resolve a URL by explicit channel type.
     */
    public function resolve(string $type, string $path = '/', ?string $storeCode = null): string
    {
        return match ($type) {
            CdnUrlType::DYNAMIC => $this->dynamic($path, $storeCode),
            CdnUrlType::MEDIA => $this->media($path),
            CdnUrlType::STATIC => $this->staticAsset($path),
            default => throw new InvalidArgumentException('Unsupported CDN URL type: ' . $type),
        };
    }

    /**
     * Return whether CDN mode is explicitly enabled.
     */
    public function isEnabled(): bool
    {
        return (bool) ($this->config['enabled'] ?? false);
    }

    /**
     * Resolve the dynamic base URL for a store view, falling back to default.
     */
    private function dynamicBaseUrl(?string $storeCode): string
    {
        $dynamic = is_array($this->config['dynamic'] ?? null) ? $this->config['dynamic'] : [];
        $storeUrls = is_array($dynamic['store_base_urls'] ?? null) ? $dynamic['store_base_urls'] : [];

        if ($storeCode !== null && isset($storeUrls[$storeCode]) && is_string($storeUrls[$storeCode])) {
            return $this->baseUrl($storeUrls[$storeCode]);
        }

        return $this->baseUrl((string) ($dynamic['base_url'] ?? ''));
    }

    /**
     * Resolve a media/static asset URL with its configured path prefix.
     */
    private function assetUrl(string $type, string $path): string
    {
        $section = is_array($this->config[$type] ?? null) ? $this->config[$type] : [];
        $baseUrl = $this->baseUrl((string) ($section['base_url'] ?? ''));
        $prefix = (string) ($section['path_prefix'] ?? '');
        $path = $this->normalisePath($path);

        if ($prefix !== '' && !str_starts_with($path, rtrim($prefix, '/') . '/')) {
            $path = rtrim($prefix, '/') . '/' . ltrim($path, '/');
        }

        return $this->join($baseUrl, $path);
    }

    private function baseUrl(string $baseUrl): string
    {
        return rtrim(trim($baseUrl), '/');
    }

    private function join(string $baseUrl, string $path): string
    {
        $path = $this->normalisePath($path);

        if ($baseUrl === '') {
            return $path;
        }

        return $baseUrl . '/' . ltrim($path, '/');
    }

    private function normalisePath(string $path): string
    {
        $path = trim($path);
        if ($path === '') {
            return '/';
        }

        if (preg_match('#^https?://#i', $path) === 1) {
            return $path;
        }

        return '/' . ltrim($path, '/');
    }
}
