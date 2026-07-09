<?php

declare(strict_types=1);

namespace Zoosper\Admin\Controller;

use RuntimeException;
use Zoosper\Admin\Audit\AuditLogger;
use Zoosper\Admin\Layout\AdminLayout;
use Zoosper\Auth\Service\CsrfTokenManager;
use Zoosper\Auth\Service\SessionGuard;
use Zoosper\Core\Http\Request;
use Zoosper\Core\Http\Response;
use Zoosper\Site\Repository\SiteRepository;
use Zoosper\Theme\Theme\ThemeRepository;

final readonly class ThemeAdminController
{
    public function __construct(
        private SessionGuard $guard,
        private CsrfTokenManager $csrf,
        private AdminLayout $layout,
        private ThemeRepository $themes,
        private SiteRepository $sites,
        private ?AuditLogger $auditLogger = null,
    ) {
    }

    public function index(Request $request): Response
    {
        $user = $this->guard->requirePermission('settings.manage');
        if ($user === null) {
            return Response::redirect('/admin/login');
        }

        $rows = '';
        foreach ($this->themes->all() as $theme) {
            $rows .= '<tr><td><code>' . $this->e($theme['code']) . '</code></td><td>' . $this->e($theme['name']) . '</td><td>' . $this->e($theme['version']) . '</td><td>' . $this->e($theme['path']) . '</td></tr>';
        }
        if ($rows === '') {
            $rows = '<tr><td colspan="4">No installed themes found.</td></tr>';
        }

        $siteForms = '';
        foreach ($this->sites->allActive() as $site) {
            $siteForms .= $this->siteThemeForm($site->id, $site->name, $site->themeCode);
        }

        $content = '<h2>Installed Themes</h2><table><thead><tr><th>Code</th><th>Name</th><th>Version</th><th>Path</th></tr></thead><tbody>' . $rows . '</tbody></table>';
        $content .= '<h2>Assign Theme to Site</h2>' . ($siteForms ?: '<p>No active sites found.</p>');

        return Response::html($this->layout->render('Themes', $content, $user, 'themes'));
    }

    public function assign(Request $request): Response
    {
        $user = $this->guard->requirePermission('settings.manage');
        if ($user === null) {
            return Response::redirect('/admin/login');
        }

        $form = $request->form();
        if (!$this->csrf->isValid((string) ($form['_csrf_token'] ?? ''))) {
            return Response::html('Invalid security token.', 419);
        }

        $siteId = (int) ($form['site_id'] ?? 0);
        $themeCode = trim((string) ($form['theme_code'] ?? ''));

        try {
            if ($siteId <= 0) {
                throw new RuntimeException('Invalid site.');
            }
            if (!$this->themes->exists($themeCode)) {
                throw new RuntimeException('Theme does not exist: ' . $themeCode);
            }
            $this->sites->updateTheme($siteId, $themeCode);
            $this->auditLogger?->record($user, 'site.theme.updated', 'site', (string) $siteId, 'Updated site theme', ['theme_code' => $themeCode], $request);
            return Response::redirect('/admin/themes');
        } catch (RuntimeException $exception) {
            return Response::html($this->layout->render('Theme Error', '<p class="error">' . $this->e($exception->getMessage()) . '</p><p><a href="/admin/themes">Back to themes</a></p>', $user, 'themes'), 422);
        }
    }

    private function siteThemeForm(int $siteId, string $siteName, string $currentTheme): string
    {
        $token = $this->e($this->csrf->token());
        $options = '';
        foreach ($this->themes->all() as $theme) {
            $selected = $theme['code'] === $currentTheme ? ' selected' : '';
            $options .= '<option value="' . $this->e($theme['code']) . '"' . $selected . '>' . $this->e($theme['name']) . ' (' . $this->e($theme['code']) . ')</option>';
        }

        return <<<HTML
<form method="post" action="/admin/themes/assign" class="card">
    <input type="hidden" name="_csrf_token" value="{$token}">
    <input type="hidden" name="site_id" value="{$siteId}">
    <h3>{$this->e($siteName)}</h3>
    <label>Theme <select name="theme_code">{$options}</select></label>
    <button type="submit">Save theme</button>
</form>
HTML;
    }

    private function e(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}
