<?php

declare(strict_types=1);

namespace Zoosper\Admin\Controller;

use RuntimeException;
use Zoosper\Admin\Audit\AuditLogger;
use Zoosper\Admin\Layout\AdminLayout;
use Zoosper\Admin\UI\AdminViewRenderer;
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
        private ?AdminViewRenderer $views = null,
    ) {
    }

    public function index(Request $request): Response
    {
        $user = $this->guard->requirePermission('settings.manage');
        if ($user === null) {
            return Response::redirect('/admin/login');
        }

        if ($this->views !== null) {
            return Response::html($this->views->render(
                title: 'Themes',
                template: 'zoosper-theme::admin/themes/index',
                data: [
                    'themes' => $this->themes->all(),
                    'sites' => $this->sites->allActive(),
                    'csrfToken' => $this->csrf->token(),
                ],
                user: $user,
                active: 'themes',
            ));
        }

        return Response::html($this->layout->render('Themes', '<p>Theme admin view renderer is not configured.</p>', $user, 'themes'));
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
            return Response::html($this->layout->render(
                'Theme Error',
                '<p class="error">' . htmlspecialchars($exception->getMessage(), ENT_QUOTES, 'UTF-8') . '</p><p><a href="/admin/themes">Back to themes</a></p>',
                $user,
                'themes',
            ), 422);
        }
    }
}
