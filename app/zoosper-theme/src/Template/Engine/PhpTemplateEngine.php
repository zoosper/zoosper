<?php

declare(strict_types=1);

namespace Zoosper\Theme\Template\Engine;

/**
 * Legacy PHP template engine.
 *
 * This keeps existing `.php` theme and module templates working while Zoosper
 * moves towards modern engines such as Latte. It is intentionally small and
 * should eventually become a fallback rather than the primary authoring format.
 */
final readonly class PhpTemplateEngine implements TemplateEngineInterface
{
    /** @return list<string> */
    public function extensions(): array
    {
        return ['php'];
    }

    /** @param array<string, mixed> $data */
    public function renderFile(string $path, array $data): string
    {
        extract($data, EXTR_SKIP);

        ob_start();
        require $path;

        return (string) ob_get_clean();
    }
}
