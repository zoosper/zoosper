<?php

declare(strict_types=1);

namespace Zoosper\Core\Html;

use HTMLPurifier;
use HTMLPurifier_Config;
use Zoosper\Core\Exception\ZoosperException;
use Zoosper\Core\Filesystem\ProjectPathResolver;

/**
 * HTMLPurifier-backed sanitizer for CMS body HTML.
 *
 * Runtime cache paths are resolved against the project root, never against the
 * current working directory. This prevents browser requests from creating
 * public/var/cache/htmlpurifier when PHP-FPM has public/ as its working path.
 */
final readonly class HtmlPurifierSanitizer implements HtmlSanitizerInterface
{
    private HTMLPurifier $purifier;

    /**
     * @param array<string, mixed> $options
     */
    public function __construct(array $options = [])
    {
        if (!class_exists(HTMLPurifier::class) || !class_exists(HTMLPurifier_Config::class)) {
            throw new ZoosperException(
                message: 'HTMLPurifier is not installed.',
                context: 'The htmlpurifier sanitizer driver requires the ezyang/htmlpurifier Composer package.',
                suggestion: 'Run composer require ezyang/htmlpurifier or use HTML_SANITIZER_DRIVER=basic for local fallback.',
            );
        }

        $paths = ProjectPathResolver::fromCoreModule();
        $cachePath = $this->normaliseRuntimeCachePath((string) ($options['cache_path'] ?? 'var/cache/htmlpurifier'), $paths);

        if (!is_dir($cachePath) && !mkdir($cachePath, 0775, true) && !is_dir($cachePath)) {
            throw new ZoosperException(
                message: 'Unable to create HTML sanitizer cache directory.',
                context: 'HTMLPurifier requires a writable cache directory outside the public webroot.',
                suggestion: 'Ensure var/cache/htmlpurifier is writable by the PHP process.',
                details: ['cache_path' => $cachePath],
            );
        }

        $config = HTMLPurifier_Config::createDefault();
        $config->set('Cache.SerializerPath', $cachePath);
        $config->set('HTML.Allowed', (string) ($options['allowed_elements'] ?? 'p,br,strong,b,em,i,u,ul,ol,li,a[href|title|target|rel],h2,h3,h4,h5,h6,blockquote,pre,code,img[src|alt|title|width|height],table,thead,tbody,tr,th,td'));
        $config->set('URI.AllowedSchemes', $this->allowedSchemes((string) ($options['allowed_schemes'] ?? 'http,https,mailto,tel')));

        if (array_key_exists('strip_empty', $options)) {
            $config->set('AutoFormat.RemoveEmpty', (bool) $options['strip_empty']);
        }

        $this->purifier = new HTMLPurifier($config);
    }

    public function sanitise(string $html): SanitizedHtml
    {
        return new SanitizedHtml($this->purifier->purify($html));
    }

    /**
     * @return array<string, bool>
     */
    private function allowedSchemes(string $schemes): array
    {
        $allowed = [];
        foreach (array_filter(array_map('trim', explode(',', $schemes))) as $scheme) {
            $allowed[$scheme] = true;
        }

        return $allowed;
    }

    private function normaliseRuntimeCachePath(string $cachePath, ProjectPathResolver $paths): string
    {
        if (str_starts_with($cachePath, '/')) {
            return rtrim($cachePath, '/');
        }

        $cachePath = trim($cachePath, '/');
        if (str_starts_with($cachePath, 'var/')) {
            return $paths->varPath(substr($cachePath, 4));
        }

        return $paths->varPath($cachePath);
    }
}
