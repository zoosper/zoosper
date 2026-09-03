<?php

declare(strict_types=1);

namespace Zoosper\Theme\Admin\Controller;

use RuntimeException;
use Zoosper\Audit\Contract\AuditLoggerInterface;
use Zoosper\Admin\Layout\AdminLayout;
use Zoosper\Admin\UI\AdminViewRenderer;
use Zoosper\Auth\Service\CsrfTokenManager;
use Zoosper\Auth\Service\SessionGuard;
use Zoosper\Core\Http\Request;
use Zoosper\Core\Http\Response;
use Zoosper\Core\Url\AdminUrlGenerator;
use Zoosper\Site\Repository\SiteRepository;
use Zoosper\Theme\Application\ThemeAssignmentService;
use Zoosper\Theme\Theme\ThemeRepository;

final readonly class ThemeAdminController
{
    public function __construct(
        private SessionGuard $guard,
        private CsrfTokenManager $csrf,
        private AdminLayout $layout,
        private ThemeRepository $themes,
        private SiteRepository $sites,
        private ThemeAssignmentService $assignment,
        private ?AuditLoggerInterface $auditLogger = null,
        private ?AdminViewRenderer $views = null,
        private ?AdminUrlGenerator $adminUrls = null,
    ) {
    }

    public function index(Request $request): Response
    {
        $user = $this->currentAdminUser();

        if ($this->views !== null) {
            return Response::html($this->views->render(
                title: '',
                template: 'zoosper-theme::admin/themes/index',
                data: [
                    'themes' => $this->themes->all(),
                    'sites' => $this->sites->allActive(),
                    'csrfToken' => $this->csrf->token(),
                    'assignUrl' => $this->adminUrls?->url('themes/assign') ?? '/admin/themes/assign',
                ],
                user: $user,
                active: 'themes',
            ));
        }

        return Response::html($this->layout->render('Themes', '<p>Theme admin view renderer is not configured.</p>', $user, 'themes'));
    }

    public function assign(Request $request): Response
    {
        $user = $this->currentAdminUser();

        $form = $request->form();

        $siteId = (int) ($form['site_id'] ?? 0);
        $themeCode = trim((string) ($form['theme_code'] ?? ''));

        try {
            if ($siteId <= 0) {
                throw new RuntimeException('Invalid site.');
            }
            if (!$this->themes->exists($themeCode)) {
                throw new RuntimeException('Theme does not exist: ' . $themeCode);
            }

            $this->assignment->assign($siteId, $themeCode);
            $this->auditLogger?->logAction($user->id, $user->email, 'site.theme.updated', 'site', (string) $siteId, 'Updated site theme.', ['theme_code' => $themeCode]);

            return Response::redirect($this->adminUrls?->url('themes') ?? '/admin/themes');
        } catch (RuntimeException $exception) {
            return Response::html($this->layout->render(
                'Theme Error',
                '<p class="error">' . htmlspecialchars($exception->getMessage(), ENT_QUOTES, 'UTF-8') . '</p><p><a href="' . htmlspecialchars($this->adminUrls?->url('themes') ?? '/admin/themes', ENT_QUOTES, 'UTF-8') . '">Back to themes</a></p>',
                $user,
                'themes',
            ), 422);
        }
    }
    /**
     * Return the authenticated admin user after the middleware permission gate.
     */
    private function currentAdminUser(): \Zoosper\Auth\Model\AdminUser
    {
        $user = $this->guard->user();
        if ($user === null) {
            throw new RuntimeException('Authenticated admin user required after middleware guard.');
        }

        return $user;
    }
}










