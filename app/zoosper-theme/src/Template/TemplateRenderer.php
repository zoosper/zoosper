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
    public function renderLayout(string $layout, string $content, array $data = [], ?string $themeCode = null): string
    {
        $data['content'] = $content;
        return $this->render($layout, $data, $themeCode);
    }

    /** @param array<string, mixed> $data */
    public function partial(string $template, array $data = [], ?string $themeCode = null): string
    {
        return $this->render('partials/' . ltrim($template, '/'), $data, $themeCode);
    }

    /** @param array<string, mixed> $data */
    private function renderFromTheme(Theme $theme, string $template, array $data): string
    {
        $path = $theme->templatePath($template);
        if (!is_file($path)) {
            throw new RuntimeException('Template does not exist: ' . $template . ' in theme ' . $theme->code);
        }

        $e = static fn (mixed $value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
        $partial = fn (string $name, array $partialData = []): string => $this->partial($name, array_merge($data, $partialData), $theme->code);
        extract($data, EXTR_SKIP);

        ob_start();
        require $path;
        return (string) ob_get_clean();
    }
}
