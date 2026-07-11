<?php

declare(strict_types=1);

namespace Zoosper\Admin\Layout;

use Zoosper\Admin\Asset\AdminAssetTemplateRenderer;
use Zoosper\Admin\Asset\AdminAssetViewDataProvider;
use Zoosper\Admin\Message\FlashMessageRenderer;
use Zoosper\Admin\Message\FlashMessageStoreInterface;
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
        private ?AdminAssetTemplateRenderer $assetRenderer = null,
        private ?AdminAssetViewDataProvider $assetViewData = null,
        private ?FlashMessageStoreInterface $flashMessages = null,
        private ?FlashMessageRenderer $flashRenderer = null,
    ) {
    }

    /**
     * Render the admin shell around a trusted admin content fragment.
     *
     * Flash messages are pulled exactly once per rendered layout. They are short
     * admin UI notices only and must never include secrets, OTPs, reset tokens,
     * payment data, raw exception traces, session IDs or SMTP passwords.
     */
    public function render(string $title, string $content, ?AdminUser $user, string $active = 'dashboard'): string
    {
        $userName = $user !== null ? $user->name : 'Guest';
        $navigation = $user !== null ? $this->navigation($user, $active) : '';
        $version = 'Zoosper CMS ' . (string) ($this->config?->get('app.version', '0.16.0-dev') ?? '0.16.0-dev');
        $templates = $this->templates ?? new TemplateRenderer(new ThemeResolver(dirname(__DIR__, 5) . '/themes/admin', 'default'));
        $assetData = $this->assetViewData?->data() ?? [
            'stylesheets' => [],
            'scripts' => [],
        ];
        $flashMessagesHtml = $this->flashRenderer?->render($this->flashMessages?->pull() ?? []) ?? '';

        return $templates->render('layout.php', [
            'title' => $title,
            'navigation' => $navigation,
            'content' => $content,
            'userName' => $userName,
            'version' => $version,
            'stylesheets' => $assetData['stylesheets'],
            'scripts' => $assetData['scripts'],
            'assetStylesHtml' => $this->assetRenderer?->stylesHtml() ?? '',
            'assetScriptsHtml' => $this->assetRenderer?->scriptsHtml() ?? '',
            'flashMessagesHtml' => $flashMessagesHtml,
        ]);
    }

    /**
     * Build the admin navigation HTML for the current user.
     */
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

        $html .= $this->logoutForm();

        return $html . '</nav>';
    }

    /**
     * Render one escaped menu link and mark the active item.
     */
    private function navigationLink(AdminMenuItem $item, string $active): string
    {
        $url = htmlspecialchars($item->url, ENT_QUOTES, 'UTF-8');
        $label = htmlspecialchars($item->label, ENT_QUOTES, 'UTF-8');
        $class = $item->code === $active ? ' class="active"' : '';

        return '<a href="' . $url . '"' . $class . '>' . $label . '</a>';
    }

    /**
     * Render the POST-only logout form for the admin navigation.
     */
    private function logoutForm(): string
    {
        $action = htmlspecialchars($this->adminUrl('/logout'), ENT_QUOTES, 'UTF-8');

        return '<div class="menu-group">Account</div>'
            . '<form method="post" action="' . $action . '" class="admin-nav-logout-form">'
            . '<button type="submit" class="admin-nav-logout-button">Logout</button>'
            . '</form>';
    }

    /**
     * Build an admin URL from config/admin.php instead of hard-coding /admin.
     */
    private function adminUrl(string $path): string
    {
        $adminConfig = $this->config?->array('admin') ?? [];
        $basePath = (string) ($adminConfig['base_path'] ?? '/admin');

        return rtrim($basePath, '/') . '/' . ltrim($path, '/');
    }
}
