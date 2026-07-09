<?php

declare(strict_types=1);

namespace Zoosper\Admin\UI;

use Zoosper\Theme\Template\TemplateRenderer;

final readonly class AdminComponentRenderer
{
    public function __construct(private TemplateRenderer $templates)
    {
    }

    /** @param array<string, mixed> $data */
    public function render(string $component, array $data = []): string
    {
        return $this->templates->render('components/' . ltrim($component, '/'), $data, 'default', 'admin.component');
    }
}
