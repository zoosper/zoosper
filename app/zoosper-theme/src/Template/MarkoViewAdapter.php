<?php

declare(strict_types=1);

namespace Zoosper\Theme\Template;

use Marko\Routing\Http\Response;
use Marko\View\ViewInterface;

/**
 * Compatibility adapter exposing Zoosper's mature template runtime through
 * Marko's engine-independent ViewInterface.
 *
 * This is additive: existing Zoosper renderers remain authoritative while new
 * code may begin depending on the Marko contract. A later aligned
 * marko/view-latte release can replace this binding without changing callers.
 */
final readonly class MarkoViewAdapter implements ViewInterface
{
    public function __construct(
        private TemplateRenderer $templates,
        private string $themeCode = 'default',
        private string $area = 'default',
    ) {
    }

    /** @param array<string, mixed> $data */
    public function render(string $template, array $data = []): Response
    {
        return new Response($this->renderToString($template, $data));
    }

    /** @param array<string, mixed> $data */
    public function renderToString(string $template, array $data = []): string
    {
        return $this->templates->render(
            $this->normaliseTemplate($template),
            $data,
            $this->themeCode,
            $this->area,
        );
    }

    private function normaliseTemplate(string $template): string
    {
        $template = trim($template);
        if ($template === '' || !str_contains($template, '::')) {
            throw new \InvalidArgumentException(
                'Marko view templates must use the module::path naming convention.',
            );
        }

        return $template;
    }
}










