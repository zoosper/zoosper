<?php

declare(strict_types=1);

namespace Zoosper\Editor\Config;

use Zoosper\Core\Config\ConfigRepository;
use Zoosper\ScopedConfig\ScopeConfigRepository;
use Zoosper\ScopedConfig\ScopeContext;

/** Resolves editor selection with scoped database precedence. */
final readonly class ContentEditorRuntimeConfig
{
    public function __construct(
        private ConfigRepository $project,
        private ?ScopeConfigRepository $scoped = null,
        private ?ScopeContext $scope = null,
    ) {
    }

    public function preferred(): string
    {
        return $this->value('editor.default_editor', 'editorjs');
    }

    public function fallback(): string
    {
        return $this->value('editor.fallback_editor', 'textarea');
    }

    private function value(string $path, string $default): string
    {
        $projectValue = trim((string) ($this->project->get($path, $default) ?? $default));
        if ($projectValue === '') {
            $projectValue = $default;
        }

        if ($this->scoped === null) {
            return $projectValue;
        }

        $value = trim((string) ($this->scoped->get(
            $path,
            $this->scope ?? ScopeContext::default(),
            $projectValue,
        ) ?? $projectValue));

        return $value !== '' ? $value : $projectValue;
    }
}
