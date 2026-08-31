<?php

declare(strict_types=1);

namespace Zoosper\Theme\Config;

use Zoosper\Core\Config\ConfigRepository;
use Zoosper\ScopedConfig\ScopeConfigRepository;
use Zoosper\ScopedConfig\ScopeContext;

/** Resolves Theme-owned template runtime settings with scoped DB precedence. */
final readonly class TemplateRuntimeConfig
{
    public function __construct(
        private string $basePath,
        private ConfigRepository $project,
        private ?ScopeConfigRepository $scoped = null,
        private ?ScopeContext $scope = null,
    ) {
    }

    public function engine(): string
    {
        $engine = strtolower(trim($this->value('template.engine', 'latte')));

        return in_array($engine, ['latte', 'php'], true) ? $engine : 'latte';
    }

    public function cacheDirectory(): string
    {
        $relative = trim($this->value('template.template_cache_path', 'var/cache/templates'));
        if ($relative === '') {
            $relative = 'var/cache/templates';
        }

        return rtrim($this->basePath, '/') . '/' . ltrim($relative, '/');
    }

    private function value(string $path, string $default): string
    {
        $projectValue = (string) ($this->project->get($path, $default) ?? $default);
        if ($this->scoped === null) {
            return $projectValue;
        }

        return (string) ($this->scoped->get($path, $this->scope ?? ScopeContext::default(), $projectValue) ?? $projectValue);
    }
}










