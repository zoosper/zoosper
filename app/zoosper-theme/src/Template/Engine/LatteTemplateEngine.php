<?php

declare(strict_types=1);

namespace Zoosper\Theme\Template\Engine;

use Latte\Engine as LatteEngine;
use Zoosper\Core\Exception\ZoosperException;

/**
 * Latte template engine adapter.
 *
 * Latte is the recommended first modern template engine for Zoosper because it
 * keeps templates readable, separates HTML from PHP classes and provides strong
 * escaping behaviour. This adapter is intentionally replaceable through the
 * module service-provider system.
 */
final class LatteTemplateEngine implements TemplateEngineInterface
{
    private LatteEngine $latte;

    public function __construct(private readonly string $cacheDirectory)
    {
        if (!class_exists(LatteEngine::class)) {
            throw new ZoosperException(
                message: 'Latte template engine package is not installed.',
                context: 'Zoosper selected the Latte template engine, but Latte\\Engine could not be loaded from Composer autoload.',
                suggestion: 'Run `composer require latte/latte:^3.1` and `composer dump-autoload`, then run `php tools/verify-latte-template-engine.php`.',
                docsUrl: 'docs/operations/latte-template-engine-setup.md',
                details: ['expected_class' => LatteEngine::class],
            );
        }

        if (!is_dir($this->cacheDirectory) && !mkdir($this->cacheDirectory, 0775, true) && !is_dir($this->cacheDirectory)) {
            throw new ZoosperException(
                message: 'Unable to create Latte template cache directory.',
                context: 'Latte compiles templates to PHP and needs a writable cache directory.',
                suggestion: 'Create the configured directory and make it writable by the PHP user: ' . $this->cacheDirectory,
                docsUrl: 'docs/operations/latte-template-engine-setup.md',
                details: ['cache_directory' => $this->cacheDirectory],
            );
        }

        $this->latte = new LatteEngine();
        $this->latte->setTempDirectory($this->cacheDirectory);
    }

    /** @return list<string> */
    public function extensions(): array
    {
        return ['latte'];
    }

    /** @param array<string, mixed> $data */
    public function renderFile(string $path, array $data): string
    {
        try {
            return $this->latte->renderToString($path, $data);
        } catch (\Throwable $exception) {
            throw new ZoosperException(
                message: 'Latte template rendering failed: ' . $path,
                context: 'The Latte adapter attempted to render a template file but Latte raised an exception.',
                suggestion: 'Check the Latte syntax, variables passed to the template and cache directory permissions. Then run `php tools/verify-latte-template-engine.php`.',
                docsUrl: 'docs/operations/latte-template-engine-setup.md',
                details: ['template' => $path, 'cache_directory' => $this->cacheDirectory],
                previous: $exception,
            );
        }
    }
}
