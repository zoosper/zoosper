<?php

declare(strict_types=1);

namespace Zoosper\Theme\Template;

use RuntimeException;
use Zoosper\Theme\Theme\Theme;
use Zoosper\Theme\Theme\ThemeResolver;

final readonly class TemplateRenderer
{
    public function __construct(private ThemeResolver $themes)
    {
    }

    /** @param array<string, mixed> $data */
    public function render(string $template, array $data = [], ?string $themeCode = null): string
    {
        $theme = $this->themes->resolve($themeCode);
        return $this->renderFromTheme($theme, $template, $data);
    }

    /** @param array<string, mixed> $data */
    private function renderFromTheme(Theme $theme, string $template, array $data): string
    {
        $path = $theme->templatePath($template);

        if (!is_file($path)) {
            throw new RuntimeException('Template does not exist: ' . $template . ' in theme ' . $theme->code);
        }

        $e = static fn (mixed $value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
        extract($data, EXTR_SKIP);

        ob_start();
        require $path;
        return (string) ob_get_clean();
    }
}
