<?php

declare(strict_types=1);

namespace Zoosper\Admin\Controller;

use DateTimeInterface;
use InvalidArgumentException;
use RuntimeException;
use Zoosper\Admin\Announcement\AdminAnnouncementRepository;
use Zoosper\Admin\Layout\AdminLayout;
use Zoosper\Admin\UI\AdminViewRenderer;
use Zoosper\Auth\Model\AdminUser;
use Zoosper\Auth\Service\CsrfTokenManager;
use Zoosper\Auth\Service\SessionGuard;
use Zoosper\Core\Audit\AuditLoggerInterface;
use Zoosper\Core\Http\Request;
use Zoosper\Core\Http\Response;
use Zoosper\Core\Message\FlashMessageStoreInterface;
use Zoosper\Core\Url\AdminUrlGenerator;

final readonly class AnnouncementAdminController
{
    public function __construct(
        private SessionGuard $guard,
        private AdminAnnouncementRepository $announcements,
        private CsrfTokenManager $csrf,
        private AdminLayout $layout,
        private AdminUrlGenerator $urls,
        private ?FlashMessageStoreInterface $flash = null,
        private ?AdminViewRenderer $views = null,
        private ?AuditLoggerInterface $audit = null,
    ) {
    }

    public function index(Request $request): Response
    {
        $user = $this->currentAdminUser();
        $items = $this->announcements->all();
        $counts = $this->announcements->acknowledgmentCounts();
        $editId = (int) ($request->query('id') ?? 0);
        $editItem = $editId > 0 ? $this->announcements->findById($editId) : null;

        if ($this->views !== null) {
            return Response::html($this->views->render(
                title: 'Global Announcements',
                template: 'zoosper-admin::announcements/index',
                data: [
                    'csrfToken' => $this->csrf->token(),
                    'announcements' => $items,
                    'acknowledgmentCounts' => $counts,
                    'editItem' => $editItem,
                    'saveUrl' => $this->urls->url('announcements/save'),
                    'publishUrl' => $this->urls->url('announcements/publish'),
                    'unpublishUrl' => $this->urls->url('announcements/unpublish'),
                    'archiveUrl' => $this->urls->url('announcements/archive'),
                    'announcementsUrl' => $this->urls->url('announcements'),
                ],
                user: $user,
                active: 'announcements',
            ));
        }

        return Response::html($this->layout->render(
            'Global Announcements',
            '<section class="card"><h1>Global Announcements</h1><p>Announcements management surface.</p></section>',
            $user,
            'announcements',
        ));
    }

    public function save(Request $request): Response
    {
        $actor = $this->currentAdminUser();
        $form = $request->form();
        $id = (int) ($form['id'] ?? 0);
        $title = trim((string) ($form['title'] ?? ''));
        $body = trim((string) ($form['body'] ?? ''));
        $status = (string) ($form['status'] ?? 'draft');

        if (!in_array($status, ['draft', 'published', 'archived'], true)) {
            $status = 'draft';
        }

        if ($title === '' || $body === '') {
            $this->flash?->error('Announcement title and body cannot be empty.', 'admin.announcements');
            return Response::redirect($this->urls->url('announcements' . ($id > 0 ? '?id=' . $id : '')), 303);
        }

        if ($id > 0) {
            $this->announcements->update($id, $title, $body, $status);
            $this->audit?->record($actor, 'announcement.updated', 'admin_announcement', (string) $id, 'Updated Global Announcement', ['title' => $title, 'status' => $status], $request);
            $this->flash?->success('Global Announcement updated.', 'admin.announcements');
        } else {
            $created = $this->announcements->create($title, $body, $status, $actor->id);
            $this->audit?->record($actor, 'announcement.created', 'admin_announcement', (string) $created->id, 'Created Global Announcement', ['title' => $title, 'status' => $status], $request);
            $this->flash?->success('Global Announcement created.', 'admin.announcements');
        }

        return Response::redirect($this->urls->url('announcements'), 303);
    }

    public function publish(Request $request): Response
    {
        $actor = $this->currentAdminUser();
        $id = (int) (($request->form())['id'] ?? 0);

        if ($id > 0) {
            $this->announcements->publish($id);
            $this->audit?->record($actor, 'announcement.published', 'admin_announcement', (string) $id, 'Published Global Announcement', [], $request);
            $this->flash?->success('Global Announcement published.', 'admin.announcements');
        }

        return Response::redirect($this->urls->url('announcements'), 303);
    }

    public function unpublish(Request $request): Response
    {
        $actor = $this->currentAdminUser();
        $id = (int) (($request->form())['id'] ?? 0);

        if ($id > 0) {
            $this->announcements->unpublish($id);
            $this->audit?->record($actor, 'announcement.unpublished', 'admin_announcement', (string) $id, 'Unpublished Global Announcement', [], $request);
            $this->flash?->success('Global Announcement moved to draft.', 'admin.announcements');
        }

        return Response::redirect($this->urls->url('announcements'), 303);
    }

    public function archive(Request $request): Response
    {
        $actor = $this->currentAdminUser();
        $id = (int) (($request->form())['id'] ?? 0);

        if ($id > 0) {
            $this->announcements->archive($id);
            $this->audit?->record($actor, 'announcement.archived', 'admin_announcement', (string) $id, 'Archived Global Announcement', [], $request);
            $this->flash?->success('Global Announcement archived.', 'admin.announcements');
        }

        return Response::redirect($this->urls->url('announcements'), 303);
    }

    public function active(Request $request): Response
    {
        $user = $this->guard->user();
        if ($user === null) {
            return Response::json(['active' => false], 401);
        }

        $active = $this->announcements->findUnacknowledgedForUser($user->id);
        if ($active === null) {
            return Response::json(['active' => false]);
        }

        return Response::json([
            'active' => true,
            'announcement' => [
                'id' => $active->id,
                'title' => $active->title,
                'body' => $active->body,
                'published_at' => $active->publishedAt?->format(DateTimeInterface::ATOM),
            ],
        ]);
    }

    public function acknowledge(Request $request): Response
    {
        $user = $this->guard->user();
        if ($user === null) {
            return Response::json(['success' => false, 'error' => 'Unauthorized'], 401);
        }

        $form = $request->form();
        $announcementId = (int) ($form['announcement_id'] ?? $request->query('announcement_id') ?? 0);

        if ($announcementId > 0) {
            $this->announcements->acknowledge($announcementId, $user->id);
            $this->audit?->record($user, 'announcement.acknowledged', 'admin_announcement', (string) $announcementId, 'Acknowledged Global Announcement', [], $request);
        }

        $isJson = str_contains((string) ($request->header('accept') ?? ''), 'application/json')
            || str_contains((string) ($request->header('x-requested-with') ?? ''), 'XMLHttpRequest');

        if ($isJson) {
            return Response::json(['success' => true, 'announcement_id' => $announcementId]);
        }

        return Response::redirect($this->urls->url(), 303);
    }

    private function currentAdminUser(): AdminUser
    {
        $user = $this->guard->user();
        if ($user === null) {
            throw new RuntimeException('Authenticated admin user required after middleware guard.');
        }

        return $user;
    }
}
