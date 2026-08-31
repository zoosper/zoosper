<?php

declare(strict_types=1);

namespace Zoosper\Theme\Template\Engine;

use Zoosper\Errors\ZoosperException;

/** Registry of available template engines by file extension. */
final class TemplateEngineRegistry
{
    /** @var array<string, TemplateEngineInterface> */
    private array $engines = [];

    /** @var list<string> */
    private array $priority = [];

    public function __construct(TemplateEngineInterface ...$engines)
    {
        foreach ($engines as $engine) {
            $this->register($engine);
        }
    }

    /** @param list<string> $priority */
    public function prioritise(array $priority): self
    {
        $this->priority = array_values(array_unique(array_map(
            static fn (string $extension): string => strtolower(ltrim($extension, '.')),
            $priority,
        )));

        return $this;
    }

    public function register(TemplateEngineInterface $engine): void
    {
        foreach ($engine->extensions() as $extension) {
            $extension = strtolower(ltrim($extension, '.'));
            if ($extension === '') {
                continue;
            }
            $this->engines[$extension] = $engine;
        }
    }

    public function forPath(string $path): TemplateEngineInterface
    {
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        if ($extension !== '' && isset($this->engines[$extension])) {
            return $this->engines[$extension];
        }

        throw new ZoosperException(
            message: 'No template engine is registered for template: ' . $path,
            context: 'TemplateRenderer resolved a template file, but no engine is registered for its extension.',
            suggestion: 'Register an engine for the extension in a module config/services.php file, or use a supported extension: ' . implode(', ', $this->extensions()),
            docsUrl: 'docs/operations/template-engine-selection.md',
            details: ['template' => $path, 'extension' => $extension, 'registered_extensions' => $this->extensions()],
        );
    }

    /** @return list<string> */
    public function extensions(): array
    {
        $extensions = array_keys($this->engines);
        $ordered = array_values(array_intersect($this->priority, $extensions));
        $remaining = array_values(array_diff($extensions, $ordered));
        sort($remaining);

        return array_merge($ordered, $remaining);
    }
}










