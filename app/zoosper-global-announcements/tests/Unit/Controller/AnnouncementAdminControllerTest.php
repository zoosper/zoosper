<?php

declare(strict_types=1);

namespace Zoosper\GlobalAnnouncements\Tests\Unit\Controller;

use PDO;
use Zoosper\Admin\Layout\AdminLayout;
use Zoosper\Admin\Navigation\AdminMenu;
use Zoosper\Admin\Navigation\AdminMenuLoader;
use Zoosper\Admin\Navigation\AdminNavigationRenderer;
use Zoosper\Admin\Navigation\AdminSectionBuilder;
use Zoosper\Auth\Model\AdminUser;
use Zoosper\Auth\Repository\AdminUserRepository;
use Zoosper\Auth\Service\CsrfTokenManager;
use Zoosper\Auth\Service\SessionGuard;
use Zoosper\Audit\Contract\AuditLoggerInterface;
use Zoosper\Core\Config\ConfigRepository;
use Zoosper\Core\Http\Request;
use Zoosper\Core\Module\ModuleRegistry;
use Zoosper\Core\Url\AdminUrlGenerator;
use Zoosper\GlobalAnnouncements\Announcement\AdminAnnouncementRepository;
use Zoosper\GlobalAnnouncements\Controller\AnnouncementAdminController;

beforeEach(function (): void {
    $_SESSION = [];

    $this->pdo = new PDO('sqlite::memory:');
    $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $this->pdo->exec('CREATE TABLE admin_announcements (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        title VARCHAR(190) NOT NULL,
        body TEXT NOT NULL,
        status VARCHAR(32) NOT NULL,
        published_at DATETIME NULL,
        created_by_user_id INTEGER NULL,
        created_at DATETIME NOT NULL,
        updated_at DATETIME NOT NULL
    )');

    $this->pdo->exec('CREATE TABLE admin_announcement_acknowledgments (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        announcement_id INTEGER NOT NULL,
        admin_user_id INTEGER NOT NULL,
        acknowledged_at DATETIME NOT NULL,
        UNIQUE (announcement_id, admin_user_id)
    )');

    $this->pdo->exec('CREATE TABLE admin_users (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT, email TEXT, password_hash TEXT, status TEXT, is_active INTEGER, created_at TEXT, updated_at TEXT)');
    $this->pdo->exec('CREATE TABLE admin_user_roles (user_id INTEGER, role_id INTEGER)');
    $this->pdo->exec('CREATE TABLE admin_role_permissions (role_id INTEGER, permission_id INTEGER)');
    $this->pdo->exec('CREATE TABLE admin_permissions (id INTEGER PRIMARY KEY, code TEXT, name TEXT, group_name TEXT)');
    $this->pdo->exec("INSERT INTO admin_users (id, name, email, password_hash, status, is_active) VALUES (7, 'Super Admin', 'admin@zoosper.test', '', 'active', 1)");

    $this->repository = new AdminAnnouncementRepository($this->pdo);

    $userRepo = new AdminUserRepository($this->pdo);
    $guard = new SessionGuard($userRepo, 7200);
    $_SESSION['admin_user_id'] = 7;
    $_SESSION['admin_last_activity_at'] = time();

    $csrf = new CsrfTokenManager();

    $root = dirname(__DIR__, 5);
    $moduleRegistry = new ModuleRegistry($root . '/app');
    $menu = new AdminMenu(new AdminMenuLoader($moduleRegistry), new AdminSectionBuilder());
    $layout = new AdminLayout($menu, new AdminNavigationRenderer(), announcements: $this->repository);
    $config = ConfigRepository::fromArray(['admin' => ['base_path' => '/admin']]);
    $urls = new AdminUrlGenerator($config);

    $this->audit = new class implements AuditLoggerInterface {
        public array $events = [];

        public function logAction(?int $actorAdminUserId, ?string $actorEmail, string $action, string $entityType, ?string $entityId, string $summary, array $metadata = []): void
        {
            $this->events[] = compact('actorAdminUserId', 'actorEmail', 'action', 'entityType', 'entityId', 'summary', 'metadata');
        }
    };
    $this->controller = new AnnouncementAdminController(
        guard: $guard,
        announcements: $this->repository,
        csrf: $csrf,
        layout: $layout,
        urls: $urls,
        audit: $this->audit,
    );
});

afterEach(function (): void {
    $_SESSION = [];
});

it('returns active announcement JSON for authenticated users', function (): void {
    $announcement = $this->repository->create(
        title: 'Immediate Action Required',
        body: 'System restarting in 5 minutes.',
        status: 'published',
    );

    $response = $this->controller->active(new Request('GET', '/admin/announcements/active'));

    expect($response->statusCode())->toBe(200)
        ->and($response->headers()['Content-Type'])->toBe('application/json; charset=utf-8');

    $payload = json_decode($response->body(), true);
    expect($payload['active'])->toBeTrue()
        ->and($payload['announcement']['id'])->toBe($announcement->id)
        ->and($payload['announcement']['title'])->toBe('Immediate Action Required');
});

it('processes asynchronous acknowledgment posts', function (): void {
    $announcement = $this->repository->create(
        title: 'Scheduled Maintenance',
        body: 'Maintenance at midnight.',
        status: 'published',
    );

    $request = (new Request('POST', '/admin/announcements/acknowledge'))
        ->withForm(['announcement_id' => $announcement->id]);

    $response = $this->controller->acknowledge($request);

    expect($response->statusCode())->toBe(200);
    $payload = json_decode($response->body(), true);
    expect($payload['status'])->toBe('acknowledged')
        ->and($payload['announcement_id'])->toBe($announcement->id);

    expect($this->repository->isAcknowledged($announcement->id, 7))->toBeTrue();
});

it('audits all announcement mutations through the audit contract', function (): void {
    $form = ['title' => 'Title', 'body' => 'Body', 'status' => 'draft'];
    $this->controller->save((new Request('POST', '/admin/announcements/save'))->withForm($form));
    $id = $this->repository->all()[0]->id;
    $this->controller->save((new Request('POST', '/admin/announcements/save'))->withForm(['id' => $id] + $form));
    foreach (['publish', 'unpublish', 'archive'] as $action) {
        $this->controller->$action((new Request('POST', '/admin/announcements/' . $action))->withForm(['id' => $id]));
    }

    expect(array_column($this->audit->events, 'action'))->toBe([
        'announcement.created', 'announcement.updated', 'announcement.published', 'announcement.unpublished', 'announcement.archived',
    ]);
    foreach ($this->audit->events as $event) {
        expect($event['actorAdminUserId'])->toBe(7)
            ->and($event['actorEmail'])->toBe('admin@zoosper.test')
            ->and($event['entityType'])->toBe('admin_announcement')
            ->and($event['entityId'])->toBe((string) $id);
    }
    expect($this->audit->events[0]['metadata'])->toBe(['title' => 'Title', 'status' => 'draft']);
});










