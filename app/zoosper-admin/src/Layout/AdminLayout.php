<?php

declare(strict_types=1);

namespace Zoosper\Admin\Layout;

use Zoosper\Admin\Asset\AdminAssetTemplateRenderer;
use Zoosper\Admin\Asset\AdminAssetViewDataProvider;
use Zoosper\Admin\Message\FlashMessageRenderer;
use Zoosper\Core\Message\FlashMessageStoreInterface;
use Zoosper\Admin\Navigation\AdminMenu;
use Zoosper\Admin\Navigation\AdminNavigationRenderer;
use Zoosper\Admin\Theme\AdminColourTheme;
use Zoosper\Admin\Theme\ModuleAdminColourThemeLoader;
use Zoosper\Auth\Model\AdminUser;
use Zoosper\Auth\Service\CsrfTokenManager;
use Zoosper\Core\Config\ConfigRepository;
use Zoosper\Core\Url\AdminUrlGenerator;
use Zoosper\Theme\Template\TemplateRenderer;
use Zoosper\Theme\Theme\ThemeResolver;
use Zoosper\Auth\Layout\AdminLayoutRendererInterface;

final readonly class AdminLayout implements AdminLayoutRendererInterface
{
    public function __construct(
        private AdminMenu $menu,
        private AdminNavigationRenderer $navigationRenderer = new AdminNavigationRenderer(),
        private ?ConfigRepository $config = null,
        private ?TemplateRenderer $templates = null,
        private ?AdminAssetTemplateRenderer $assetRenderer = null,
        private ?AdminAssetViewDataProvider $assetViewData = null,
        private ?FlashMessageStoreInterface $flashMessages = null,
        private ?FlashMessageRenderer $flashRenderer = null,
        private ?CsrfTokenManager $csrf = null,
        private ?AdminUrlGenerator $adminUrls = null,
        private ?ModuleAdminColourThemeLoader $colourThemes = null,
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
        $release = require dirname(__DIR__, 4) . '/config/version.php';
        $fallbackVersion = (string) ($release['version'] ?? 'unknown');
        $version = 'Zoosper CMS ' . (string) (
            $this->config?->get('app.version', $fallbackVersion)
            ?? $fallbackVersion
        );
        $templates = $this->templates ?? new TemplateRenderer(new ThemeResolver(dirname(__DIR__, 5) . '/themes/admin', 'default'));
        $assetData = $this->assetViewData?->data($active) ?? [
            'stylesheets' => [],
            'scripts' => [],
        ];
        $flashMessagesHtml = $this->flashRenderer?->render($this->flashMessages?->pull() ?? []) ?? '';
        $colourThemes = $this->colourThemes?->all() ?? [
            new AdminColourTheme('light', 'Light', 'light', 10),
            new AdminColourTheme('dark', 'Dark', 'dark', 20),
        ];

        return $templates->render('layout.php', [
            'title' => $title,
            'navigation' => $navigation,
            'content' => $content,
            'userName' => $userName,
            'version' => $version,
            'stylesheets' => $assetData['stylesheets'],
            'scripts' => $assetData['scripts'],
            'assetStylesHtml' => $this->assetRenderer?->stylesHtml($active) ?? '',
            'assetScriptsHtml' => $this->assetRenderer?->scriptsHtml($active) ?? '',
            'flashMessagesHtml' => $flashMessagesHtml,
            'adminColourThemes' => $colourThemes,
            'logoutFormHtml' => $user !== null ? $this->logoutForm() : '',
        ]);
    }

    /**
     * Build the admin navigation HTML for the current user.
     */
    private function navigation(AdminUser $user, string $active): string
    {
        return $this->navigationRenderer->render(
            $this->menu->sectionsFor($user),
            $active,
            '',
        );
    }

    /**
     * Render the POST-only logout form for the admin navigation.
     *
     * The central admin CSRF middleware validates all state-changing methods,
     * including /admin/logout. The logout form therefore must carry the current
     * session token, otherwise logout can be blocked with the 419 session-token
     * page even though the user is authenticated.
     */
    private function logoutForm(): string
    {
        $action = htmlspecialchars($this->adminUrl('/logout'), ENT_QUOTES, 'UTF-8');
        $tokenInput = '';

        if ($this->csrf !== null) {
            $tokenInput = '<input type="hidden" name="_csrf_token" value="'
                . htmlspecialchars($this->csrf->token(), ENT_QUOTES, 'UTF-8')
                . '">';
        }

        return '<form method="post" action="' . $action . '" class="admin-account-logout-form">'
            . $tokenInput
            . '<button type="submit" class="admin-account-logout-button">'
            . $this->navigationRenderer->renderIcon('logout')
            . '<span>Logout</span></button>'
            . '</form>';
    }

    /**
     * Build an admin URL from config/admin.php instead of hard-coding /admin.
     */
    private function adminUrl(string $path): string
    {
        if ($this->adminUrls !== null) {
            return $this->adminUrls->url($path);
        }

        $adminConfig = $this->config?->array('admin') ?? [];
        $basePath = (string) ($adminConfig['base_path'] ?? '/admin');

        return rtrim($basePath, '/') . '/' . ltrim($path, '/');
    }
}
