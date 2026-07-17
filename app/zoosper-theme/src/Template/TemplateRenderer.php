<?php

declare(strict_types=1);

namespace Zoosper\Theme\Template;

use RuntimeException;
use Zoosper\Core\Http\Request;
use Zoosper\Core\Module\ModuleRegistry;
use Zoosper\Core\View\TemplateViewContextProvider;
use Zoosper\Theme\Layout\LayoutUpdateRepository;
use Zoosper\Theme\Template\Engine\PhpTemplateEngine;
use Zoosper\Theme\Template\Engine\TemplateEngineRegistry;
use Zoosper\Theme\Theme\Theme;
use Zoosper\Theme\Theme\ThemeResolver;

final readonly class TemplateRenderer
{
    public function __construct(
        private ThemeResolver $themes,
        private ?ModuleRegistry $modules = null,
        private ?LayoutUpdateRepository $layoutUpdates = null,
        private ?TemplateViewContextProvider $viewContext = null,
        private ?TemplateEngineRegistry $engines = null,
    ) {
    }

    /** @param array<string, mixed> $data */
    public function render(string $template, array $data = [], ?string $themeCode = null, string $handle = 'default', ?Request $request = null): string
    {
        $theme = $this->themes->resolve($themeCode);
        return $this->renderFromTheme($theme, $template, $data, $handle, $request);
    }

    /** @param array<string, mixed> $data */
    public function renderLayout(string $layout, string $content, array $data = [], ?string $themeCode = null, string $handle = 'default', ?Request $request = null): string
    {
        $data['content'] = $content;
        return $this->render($layout, $data, $themeCode, $handle, $request);
    }

    /** @param array<string, mixed> $data */
    public function partial(string $template, array $data = [], ?string $themeCode = null, string $handle = 'default', ?Request $request = null): string
    {
        return $this->render('partials/' . ltrim($template, '/'), $data, $themeCode, $handle, $request);
    }

    /** @param array<string, mixed> $data */
    private function renderFromTheme(Theme $theme, string $template, array $data, string $handle, ?Request $request = null): string
    {
        $update = $this->layoutUpdates?->forTheme($theme, $handle);
        $template = ltrim($template, '/');

        if ($update?->isRemoved($template)) {
            return '';
        }

        $template = $update?->replacementFor($template) ?? $template;
        $path = $this->resolveTemplatePath($theme, $template);

        $siteContext = $request?->siteContext();
        $data = array_replace(
            $this->viewContext?->data(
                themeCode: $theme->code,
                routeName: $handle,
                siteContext: $siteContext,
                host: $request?->host() ?? '',
                path: $request?->path() ?? '/',
            ) ?? [],
            $data,
        );

        $data['e'] ??= static fn (mixed $value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
        $data['partial'] ??= fn (string $name, array $partialData = []): string => $this->partial($name, array_merge($data, $partialData), $theme->code, $handle, $request);
        $data['slot'] ??= fn (string $slotName, array $slotData = []): string => $this->renderSlot($theme, $handle, $slotName, array_merge($data, $slotData), $request);

        return $this->engines()->forPath($path)->renderFile($path, $data);
    }

    /** @param array<string, mixed> $data */
    private function renderSlot(Theme $theme, string $handle, string $slotName, array $data, ?Request $request = null): string
    {
        $update = $this->layoutUpdates?->forTheme($theme, $handle);
        if ($update === null) {
            return '';
        }

        $html = '';
        foreach ($update->injectionsFor($slotName) as $template) {
            $html .= $this->renderFromTheme($theme, $template, $data, $handle, $request);
        }
        return $html;
    }

    private function resolveTemplatePath(Theme $theme, string $template): string
    {
        if (str_contains($template, '::')) {
            return $this->resolveModuleTemplatePath($theme, $template);
        }

        $candidates = [];
        foreach ($this->templateVariants($template) as $variant) {
            $candidates[] = rtrim($theme->path, '/') . '/templates/overrides/' . $variant;
            $candidates[] = rtrim($theme->path, '/') . '/templates/' . $variant;
        }

        if ($theme->code !== 'default') {
            $defaultPath = dirname($theme->path) . '/default';
            foreach ($this->templateVariants($template) as $variant) {
                $candidates[] = $defaultPath . '/templates/overrides/' . $variant;
                $candidates[] = $defaultPath . '/templates/' . $variant;
            }
        }

        return $this->firstExisting($candidates, 'Template does not exist: ' . $template . ' in theme ' . $theme->code);
    }

    private function resolveModuleTemplatePath(Theme $theme, string $template): string
    {
        [$moduleName, $templatePath] = explode('::', $template, 2);
        $templatePath = ltrim($templatePath, '/');

        $candidates = [];
        foreach ($this->templateVariants($templatePath) as $variant) {
            $candidates[] = rtrim($theme->path, '/') . '/templates/modules/' . $moduleName . '/' . $variant;
        }

        if ($theme->code !== 'default') {
            $defaultPath = dirname($theme->path) . '/default';
            foreach ($this->templateVariants($templatePath) as $variant) {
                $candidates[] = $defaultPath . '/templates/modules/' . $moduleName . '/' . $variant;
            }
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
            foreach ($this->templateVariants($templatePath) as $variant) {
                $candidates[] = rtrim($module->path, '/') . '/resources/views/' . $variant;
            }
        }
        return $candidates;
    }

    /** @return list<string> */
    private function templateVariants(string $template): array
    {
        $template = ltrim($template, '/');
        $extension = pathinfo($template, PATHINFO_EXTENSION);
        if ($extension !== '') {
            return [$template];
        }

        $variants = [];
        foreach ($this->engines()->extensions() as $engineExtension) {
            $variants[] = $template . '.' . $engineExtension;
        }

        return $variants === [] ? [$template . '.php'] : $variants;
    }

    private function engines(): TemplateEngineRegistry
    {
        return $this->engines ?? new TemplateEngineRegistry(new PhpTemplateEngine());
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
