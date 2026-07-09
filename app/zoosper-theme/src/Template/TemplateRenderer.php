<?php

declare(strict_types=1);

namespace Zoosper\Theme\Template;

use RuntimeException;
use Zoosper\Core\Module\ModuleRegistry;
use Zoosper\Theme\Theme\Theme;
use Zoosper\Theme\Theme\ThemeResolver;

final readonly class TemplateRenderer
{
    public function __construct(
        private ThemeResolver $themes,
        private ?ModuleRegistry $modules = null,
    ) {
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
        $path = $this->resolveTemplatePath($theme, $template);
        $e = static fn (mixed $value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
        $partial = fn (string $name, array $partialData = []): string => $this->partial($name, array_merge($data, $partialData), $theme->code);
        extract($data, EXTR_SKIP);

        ob_start();
        require $path;
        return (string) ob_get_clean();
    }

    private function resolveTemplatePath(Theme $theme, string $template): string
    {
        $template = ltrim($template, '/');

        if (str_contains($template, '::')) {
            return $this->resolveModuleTemplatePath($theme, $template);
        }

        $candidates = [
            rtrim($theme->path, '/') . '/templates/overrides/' . $template,
            rtrim($theme->path, '/') . '/templates/' . $template,
        ];

        if ($theme->code !== 'default') {
            $defaultPath = dirname($theme->path) . '/default';
            $candidates[] = $defaultPath . '/templates/overrides/' . $template;
            $candidates[] = $defaultPath . '/templates/' . $template;
        }

        return $this->firstExisting($candidates, 'Template does not exist: ' . $template . ' in theme ' . $theme->code);
    }

    private function resolveModuleTemplatePath(Theme $theme, string $template): string
    {
        [$moduleName, $templatePath] = explode('::', $template, 2);
        $templatePath = ltrim($templatePath, '/');

        $candidates = [
            rtrim($theme->path, '/') . '/templates/modules/' . $moduleName . '/' . $templatePath,
            rtrim($theme->path, '/') . '/templates/modules/' . $moduleName . '/' . $templatePath . '.php',
        ];

        if ($theme->code !== 'default') {
            $defaultPath = dirname($theme->path) . '/default';
            $candidates[] = $defaultPath . '/templates/modules/' . $moduleName . '/' . $templatePath;
            $candidates[] = $defaultPath . '/templates/modules/' . $moduleName . '/' . $templatePath . '.php';
        }

        foreach ($this->moduleTemplateCandidates($moduleName, $templatePath) as $candidate) {
            $candidates[] = $candidate;
        }

        return $this->firstExisting($candidates, 'Module template does not exist: ' . $template);
    }

    /** @return list<string> */
    private function moduleTemplateCandidates(string $moduleName, string $templatePath): array
    {
        if ($this->modules === null) {
            return [];
        }

        $candidates = [];
        foreach ($this->modules->enabledModules() as $module) {
            if ($module->name !== $moduleName) {
                continue;
            }
            $base = rtrim($module->path, '/') . '/resources/views/' . $templatePath;
            $candidates[] = $base;
            $candidates[] = $base . '.php';
        }
        return $candidates;
    }

    /** @param list<string> $candidates */
    private function firstExisting(array $candidates, string $error): string
    {
        foreach ($candidates as $path) {
            if (is_file($path)) {
                return $path;
            }
        }
        throw new RuntimeException($error);
    }
}
