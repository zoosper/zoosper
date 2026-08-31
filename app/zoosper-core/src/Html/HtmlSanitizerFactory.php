<?php

declare(strict_types=1);

namespace Zoosper\Core\Html;

use Zoosper\Errors\ZoosperException;

/**
 * Creates the configured HTML sanitizer implementation.
 *
 * The factory keeps the selected implementation behind HtmlSanitizerInterface so
 * custom modules can replace sanitisation behaviour through `config/services.php`
 * without changing rendering code.
 *
 * SECURITY FIX (confirmed 2026-07-30, external reviewer pass): create()
 * previously built BasicHtmlSanitizer for the 'basic' driver with zero
 * guard. BasicHtmlSanitizer's own docblock explicitly states it is
 * "intentionally conservative and should not be treated as equivalent to a
 * full HTML parser" — if HTMLPurifier were ever missing from a production
 * Composer install and someone flipped the driver "to make it work," this
 * would silently apply regex-based (bypassable) sanitisation to real,
 * user-generated CMS content, with no warning.
 *
 * This is enforced HERE — not in config/html_sanitizer.php itself —
 * deliberately. That config file is still eagerly loaded by
 * ConfigRepository::fromPath() alongside every other config file the
 * moment ANY config is requested (the same trap the 2FA encryption key fix
 * hit earlier in this session, before being corrected). This factory,
 * however, is registered as a LAZY service factory in
 * app/zoosper-core/config/services.php — it is only ever invoked when
 * something actually requests HtmlSanitizerInterface — so enforcing here
 * carries zero risk of affecting unrelated config loading or unrelated
 * tests.
 *
 * No "environment" concept (local vs production) is introduced, since none
 * exists anywhere in this codebase. Instead, choosing the basic driver now
 * requires a SEPARATE, explicit acknowledgment
 * (allow_basic_driver/HTML_SANITIZER_ALLOW_BASIC_DRIVER=true) — a
 * deliberate two-step confirmation rather than one config line silently
 * accepting weaker sanitisation.
 */
final readonly class HtmlSanitizerFactory
{
    /**
     * @param array<string, mixed> $config
     */
    public function __construct(private array $config)
    {
    }

    public function create(): HtmlSanitizerInterface
    {
        $driver = strtolower((string) ($this->config['driver'] ?? 'htmlpurifier'));

        return match ($driver) {
            'htmlpurifier', 'html_purifier' => new HtmlPurifierSanitizer($this->config),
            'basic' => $this->createBasicSanitizerWithGuard(),
            default => throw new ZoosperException(
                message: 'Unsupported HTML sanitizer driver: ' . $driver,
                context: 'config/html_sanitizer.php selected a driver that Zoosper does not recognise.',
                suggestion: 'Use `htmlpurifier` for production WYSIWYG content or `basic` only for local fallback testing.',
                docsUrl: 'docs/operations/html-sanitizer-setup.md',
                details: ['driver' => $driver],
            ),
        };
    }

    /**
     * SECURITY FIX: refuses to build BasicHtmlSanitizer unless
     * 'allow_basic_driver' is explicitly true, requiring a deliberate,
     * separate acknowledgment beyond just selecting driver=basic.
     */
    private function createBasicSanitizerWithGuard(): HtmlSanitizerInterface
    {
        $explicitlyAllowed = filter_var($this->config['allow_basic_driver'] ?? false, FILTER_VALIDATE_BOOLEAN);

        if (!$explicitlyAllowed) {
            throw new ZoosperException(
                message: 'The basic HTML sanitizer driver requires explicit confirmation to use.',
                context: 'BasicHtmlSanitizer is a conservative, regex-based fallback. Its own docblock '
                    . 'states it should not be treated as equivalent to a full HTML parser. Using it to '
                    . 'sanitise real, stored CMS content risks XSS if it is ever selected unintentionally '
                    . '(for example, if HTMLPurifier is missing from a deploy and the driver is changed '
                    . '"to make it work").',
                suggestion: 'Install ezyang/htmlpurifier and use HTML_SANITIZER_DRIVER=htmlpurifier (the '
                    . 'default) for any real content. If you genuinely intend to use the basic fallback '
                    . '(for example, local development without Composer installed yet), set '
                    . 'HTML_SANITIZER_ALLOW_BASIC_DRIVER=true explicitly in your .env file to confirm '
                    . 'this choice.',
                docsUrl: 'docs/operations/html-sanitizer-setup.md',
                details: ['driver' => 'basic'],
            );
        }

        return new BasicHtmlSanitizer();
    }
}










