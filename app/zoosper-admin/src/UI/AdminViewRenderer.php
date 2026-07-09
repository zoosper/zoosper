<?php

declare(strict_types=1);

namespace Zoosper\Admin\UI;

use Zoosper\Admin\Layout\AdminLayout;
use Zoosper\Auth\Model\AdminUser;
use Zoosper\Theme\Template\TemplateRenderer;

final readonly class AdminViewRenderer
{
    public function __construct(private TemplateRenderer $templates, private AdminLayout $layout)
    {
    }

    /** @param array<string, mixed> $data */
    public function render(string $title, string $template, array $data, ?AdminUser $user, string $active = 'dashboard'): string
    {
        $content = $this->templates->render($template, $data, 'default', 'admin.content');
        return $this->layout->render($title, $content, $user, $active);
    }
}
