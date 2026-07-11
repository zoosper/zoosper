<?php

declare(strict_types=1);

namespace Zoosper\Core\Html;

use Zoosper\Core\Exception\ZoosperException;

/**
 * Creates the configured HTML sanitizer implementation.
 *
 * The factory keeps the selected implementation behind HtmlSanitizerInterface so
 * custom modules can replace sanitisation behaviour through `config/services.php`
 * without changing rendering code.
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
            'basic' => new BasicHtmlSanitizer(),
            default => throw new ZoosperException(
                message: 'Unsupported HTML sanitizer driver: ' . $driver,
                context: 'config/html_sanitizer.php selected a driver that Zoosper does not recognise.',
                suggestion: 'Use `htmlpurifier` for production WYSIWYG content or `basic` only for local fallback testing.',
                docsUrl: 'docs/operations/html-sanitizer-setup.md',
                details: ['driver' => $driver],
            ),
        };
    }
}
