<?php

declare(strict_types=1);

namespace Zoosper\Core\Html;

use HTMLPurifier;
use HTMLPurifier_Config;
use Zoosper\Core\Exception\ZoosperException;

/**
 * HTML Purifier-backed sanitizer for CMS/WYSIWYG body HTML.
 *
 * HTML Purifier should be installed through Composer using
 * `composer require ezyang/htmlpurifier`. This adapter stores no secrets and
 * must not be used to process OTPs, TOTP setup secrets, recovery-code plaintext,
 * reset tokens, SMTP passwords, payment data or customer-private values.
 */
final class HtmlPurifierSanitizer implements HtmlSanitizerInterface
{
    private HTMLPurifier $purifier;

    /**
     * @param array<string, mixed> $options
     */
    public function __construct(private readonly array $options = [])
    {
        if (!class_exists(HTMLPurifier::class) || !class_exists(HTMLPurifier_Config::class)) {
            throw new ZoosperException(
                message: 'HTML Purifier is not installed.',
                context: 'The configured HTML sanitizer driver is htmlpurifier, but the Composer package ezyang/htmlpurifier could not be found.',
                suggestion: 'Run `composer require ezyang/htmlpurifier:^4.19`, then `composer dump-autoload`, or temporarily set HTML_SANITIZER_DRIVER=basic for local development only.',
                docsUrl: 'docs/operations/html-sanitizer-setup.md',
                details: ['required_package' => 'ezyang/htmlpurifier'],
            );
        }

        $cachePath = (string) ($options['cache_path'] ?? 'var/cache/htmlpurifier');
        if (!is_dir($cachePath) && !mkdir($cachePath, 0775, true) && !is_dir($cachePath)) {
            throw new ZoosperException(
                message: 'Unable to create HTML Purifier cache directory.',
                context: 'HTML Purifier needs a writable serializer cache directory for performance.',
                suggestion: 'Create the configured cache directory and make it writable by the PHP user: ' . $cachePath,
                docsUrl: 'docs/operations/html-sanitizer-setup.md',
                details: ['cache_path' => $cachePath],
            );
        }

        $config = HTMLPurifier_Config::createDefault();
        $config->set('Cache.SerializerPath', $cachePath);
        $config->set('HTML.Allowed', (string) ($options['allowed_elements'] ?? 'p,br,strong,em,ul,ol,li,a[href],h2,h3,h4,h5,h6,blockquote,pre,code'));
        $config->set('URI.AllowedSchemes', $this->allowedSchemes((string) ($options['allowed_schemes'] ?? 'http,https,mailto,tel')));
        $config->set('AutoFormat.RemoveEmpty', (bool) ($options['strip_empty'] ?? true));

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
        $result = [];
        foreach (explode(',', $schemes) as $scheme) {
            $scheme = strtolower(trim($scheme));
            if ($scheme !== '') {
                $result[$scheme] = true;
            }
        }

        return $result;
    }
}
