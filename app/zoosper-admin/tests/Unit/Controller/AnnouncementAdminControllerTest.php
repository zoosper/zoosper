<?php

declare(strict_types=1);

namespace Zoosper\Admin\Tests\Unit\Controller;

use PDO;
use Zoosper\Admin\Announcement\AdminAnnouncementRepository;
use Zoosper\Admin\Controller\AnnouncementAdminController;
use Zoosper\Admin\Layout\AdminLayout;
use Zoosper\Admin\Navigation\AdminMenu;
use Zoosper\Admin\Navigation\AdminMenuLoader;
use Zoosper\Admin\Navigation\AdminNavigationRenderer;
use Zoosper\Admin\Navigation\AdminSectionBuilder;
use Zoosper\Admin\UI\AdminViewRenderer;
use Zoosper\Auth\Model\AdminUser;
use Zoosper\Auth\Service\CsrfTokenManager;
use Zoosper\Auth\Service\SessionGuard;
use Zoosper\Core\Http\Request;
use Zoosper\Core\Module\ModuleRegistry;
use Zoosper\Core\Url\AdminUrlGenerator;
use Zoosper\Theme\Template\TemplateRenderer;
use Zoosper\Theme\Theme\ThemeResolver;

beforeEach(function (): void {
    $this->pdo = new PDO('sqlite::memory:');
    $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $this->pdo->exec('CREATE TABLE admin_users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        email TEXT NOT NULL,
        password_hash TEXT NOT NULL,
        status TEXT NOT NULL DEFAULT "active",
        created_at TEXT,
        updated_at TEXT
    )');

    $this->pdo->exec('CREATE TABLE admin_permissions (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        code TEXT NOT NULL UNIQUE,
        label TEXT NOT NULL,
        parent_code TEXT,
        sort_order INTEGER DEFAULT 100
    )');

    $this->pdo->exec('CREATE TABLE admin_roles (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        code TEXT NOT NULL UNIQUE,
        label TEXT NOT NULL,
        created_at TEXT,
        updated_at TEXT
    )');

    $this->pdo->exec('CREATE TABLE admin_role_permissions (
        role_id INTEGER NOT NULL,
        permission_id INTEGER NOT NULL,
        PRIMARY KEY (role_id, permission_id)
    )');

    $this->pdo->exec('CREATE TABLE admin_user_roles (
        user_id INTEGER NOT NULL,
        role_id INTEGER NOT NULL,
        PRIMARY KEY (user_id, role_id)
    )');

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

    $this->pdo->exec('INSERT INTO admin_users (id, name, email, password_hash, status) VALUES
        (1, "Super Admin", "admin@zoosper.test", "hash", "active")');
    $this->pdo->exec('INSERT INTO admin_permissions (id, code, label) VALUES
        (1, "admin.access", "Admin Access"), (2, "settings.manage", "Settings Manage")');
    $this->pdo->exec('INSERT INTO admin_roles (id, code, label) VALUES (1, "super_admin", "Super Admin")');
    $this->pdo->exec('INSERT INTO admin_role_permissions (role_id, permission_id) VALUES (1, 1), (1, 2)');
    $this->pdo->exec('INSERT INTO admin_user_roles (user_id, role_id) VALUES (1, 1)');

    $this->userRepo = new \Zoosper\Auth\Repository\AdminUserRepository($this->pdo);
    $this->repository = new AdminAnnouncementRepository($this->pdo);

    $this->guard = new SessionGuard($this->userRepo);
    $user = $this->userRepo->findById(1);
    expect($user)->not->toBeNull();
    $this->user = $user;
    $this->guard->login($this->user);

    $this->csrf = new CsrfTokenManager();
    $this->urls = new AdminUrlGenerator(\Zoosper\Core\Config\ConfigRepository::fromArray(['admin' => ['base_path' => '/admin']]));

    $root = dirname(__DIR__, 5);
    $moduleRegistry = new ModuleRegistry($root . '/app');
    $menu = new AdminMenu(new AdminMenuLoader($moduleRegistry), new AdminSectionBuilder());
    $this->layout = new AdminLayout(
        menu: $menu,
        navigationRenderer: new AdminNavigationRenderer(),
        announcements: $this->repository,
    );

    $this->controller = new AnnouncementAdminController(
        guard: $this->guard,
        announcements: $this->repository,
        csrf: $this->csrf,
        layout: $this->layout,
        urls: $this->urls,
    );
});

it('renders the announcements management index', function (): void {
    $response = $this->controller->index(new Request('GET', '/admin/announcements'));

    expect($response->statusCode())->toBe(200);
    expect($response->body())->toContain('Global Announcements');
});

it('saves new announcement and handles update mutations', function (): void {
    // Create new draft
    $createReq = new Request('POST', '/admin/announcements/save', form: [
        'title' => 'Important Maintenance',
        'body' => 'All servers reboot at midnight.',
        'status' => 'draft',
    ]);
    $createRes = $this->controller->save($createReq);
    expect($createRes->statusCode())->toBe(303);

    $all = $this->repository->all();
    expect($all)->toHaveCount(1);
    expect($all[0]->title)->toBe('Important Maintenance');
    expect($all[0]->isDraft())->toBeTrue();

    $createdId = $all[0]->id;

    // Update announcement
    $updateReq = new Request('POST', '/admin/announcements/save', form: [
        'id' => (string) $createdId,
        'title' => 'Updated Maintenance Notice',
        'body' => 'Reboot postponed to 2 AM.',
        'status' => 'published',
    ]);
    $updateRes = $this->controller->save($updateReq);
    expect($updateRes->statusCode())->toBe(303);

    $updated = $this->repository->findById($createdId);
    expect($updated->title)->toBe('Updated Maintenance Notice');
    expect($updated->isPublished())->toBeTrue();
});

it('handles publish, unpublish, and archive lifecycle transitions', function (): void {
    $announcement = $this->repository->create('Feature Release', 'New editor released', 'draft');

    // Publish
    $publishRes = $this->controller->publish(new Request('POST', '/admin/announcements/publish', form: ['id' => (string) $announcement->id]));
    expect($publishRes->statusCode())->toBe(303);
    expect($this->repository->findById($announcement->id)->isPublished())->toBeTrue();

    // Unpublish
    $unpublishRes = $this->controller->unpublish(new Request('POST', '/admin/announcements/unpublish', form: ['id' => (string) $announcement->id]));
    expect($unpublishRes->statusCode())->toBe(303);
    expect($this->repository->findById($announcement->id)->isDraft())->toBeTrue();

    // Archive
    $archiveRes = $this->controller->archive(new Request('POST', '/admin/announcements/archive', form: ['id' => (string) $announcement->id]));
    expect($archiveRes->statusCode())->toBe(303);
    expect($this->repository->findById($announcement->id)->isArchived())->toBeTrue();
});

it('serves real-time active polling and records asynchronous user acknowledgment', function (): void {
    // No published announcements -> active: false
    $activeRes1 = $this->controller->active(new Request('GET', '/admin/announcements/active'));
    expect($activeRes1->statusCode())->toBe(200);
    $json1 = json_decode($activeRes1->body(), true);
    expect($json1['active'])->toBeFalse();

    // Publish announcement
    $announcement = $this->repository->create('Security Advisory', 'Update your 2FA keys.', 'published');

    // Active polling should now return the unacknowledged announcement
    $activeRes2 = $this->controller->active(new Request('GET', '/admin/announcements/active'));
    expect($activeRes2->statusCode())->toBe(200);
    $json2 = json_decode($activeRes2->body(), true);
    expect($json2['active'])->toBeTrue();
    expect($json2['announcement']['id'])->toBe($announcement->id);
    expect($json2['announcement']['title'])->toBe('Security Advisory');

    // Acknowledge announcement via AJAX POST
    $ackReq = new Request(
        'POST',
        '/admin/announcements/acknowledge',
        headers: ['accept' => 'application/json', 'x-requested-with' => 'XMLHttpRequest'],
        form: ['announcement_id' => (string) $announcement->id],
    );
    $ackRes = $this->controller->acknowledge($ackReq);
    expect($ackRes->statusCode())->toBe(200);
    $ackJson = json_decode($ackRes->body(), true);
    expect($ackJson['success'])->toBeTrue();
    expect($ackJson['announcement_id'])->toBe($announcement->id);

    // After acknowledgment, active polling should return active: false
    $activeRes3 = $this->controller->active(new Request('GET', '/admin/announcements/active'));
    $json3 = json_decode($activeRes3->body(), true);
    expect($json3['active'])->toBeFalse();
});
