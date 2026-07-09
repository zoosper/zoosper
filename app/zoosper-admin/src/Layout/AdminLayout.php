<?php

declare(strict_types=1);

namespace Zoosper\Admin\Layout;

use Zoosper\Admin\Navigation\AdminMenu;
use Zoosper\Admin\Navigation\AdminMenuItem;
use Zoosper\Auth\Model\AdminUser;
use Zoosper\Core\Config\ConfigRepository;
use Zoosper\Theme\Template\TemplateRenderer;
use Zoosper\Theme\Theme\ThemeResolver;

final readonly class AdminLayout
{
    public function __construct(
        private AdminMenu $menu,
        private ?ConfigRepository $config = null,
        private ?TemplateRenderer $templates = null,
    ) {
    }

    public function render(string $title, string $content, ?AdminUser $user, string $active = 'dashboard'): string
    {
        $userName = $user !== null ? $user->name : 'Guest';
        $navigation = $user !== null ? $this->navigation($user, $active) : '';
        $version = 'Zoosper CMS ' . (string) ($this->config?->get('app.version', '0.16.0-dev') ?? '0.16.0-dev');
        $templates = $this->templates ?? new TemplateRenderer(new ThemeResolver(dirname(__DIR__, 5) . '/themes/admin', 'default'));

        return $templates->render('layout.php', [
            'title' => $title,
            'navigation' => $navigation,
            'content' => $content,
            'userName' => $userName,
            'version' => $version,
        ]);
    }

    private function navigation(AdminUser $user, string $active): string
    {
        $html = '<nav class="admin-nav">';
        $currentGroup = null;
        foreach ($this->menu->itemsFor($user) as $item) {
            if ($item->group !== $currentGroup) {
                $currentGroup = $item->group;
                $html .= '<div class="menu-group">' . htmlspecialchars($currentGroup, ENT_QUOTES, 'UTF-8') . '</div>';
            }
            $html .= $this->navigationLink($item, $active);
        }
        return $html . '</nav>';
    }

    private function navigationLink(AdminMenuItem $item, string $active): string
    {
        $url = htmlspecialchars($item->url, ENT_QUOTES, 'UTF-8');
        $label = htmlspecialchars($item->label, ENT_QUOTES, 'UTF-8');
        $class = $item->code === $active ? ' class="active"' : '';
        return '<a href="' . $url . '"' . $class . '>' . $label . '</a>';
    }
}
