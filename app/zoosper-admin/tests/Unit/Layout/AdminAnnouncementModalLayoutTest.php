<?php

declare(strict_types=1);

namespace Zoosper\Admin\Tests\Unit\Layout;

use PDO;
use Zoosper\Admin\Layout\AdminLayout;
use Zoosper\Admin\Navigation\AdminMenu;
use Zoosper\Admin\Navigation\AdminMenuLoader;
use Zoosper\Admin\Navigation\AdminNavigationRenderer;
use Zoosper\Admin\Navigation\AdminSectionBuilder;
use Zoosper\Auth\Model\AdminUser;
use Zoosper\Core\Module\ModuleRegistry;
use Zoosper\GlobalAnnouncements\Announcement\AdminAnnouncementRepository;

beforeEach(function (): void {
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

    $this->repository = new AdminAnnouncementRepository($this->pdo);

    $this->user = new AdminUser(
        id: 42,
        email: 'user@zoosper.test',
        name: 'Logged-in Admin',
        passwordHash: 'hash',
        status: 'active',
        permissions: ['admin.access'],
    );

    $root = dirname(__DIR__, 5);
    $moduleRegistry = new ModuleRegistry($root . '/app');
    $menu = new AdminMenu(new AdminMenuLoader($moduleRegistry), new AdminSectionBuilder());
    $this->layout = new AdminLayout(
        menu: $menu,
        navigationRenderer: new AdminNavigationRenderer(),
        announcements: $this->repository,
    );
});

it('embeds visible announcement modal for authenticated offline users when unacknowledged', function (): void {
    // Create published announcement
    $announcement = $this->repository->create(
        title: 'Urgent System Maintenance',
        body: 'Please save your unsaved work immediately.',
        status: 'published',
    );

    // Render layout for user
    $html = $this->layout->render(
        title: 'Dashboard',
        content: '<p>Main content area.</p>',
        user: $this->user,
    );

    expect($html)
        ->toContain('id="admin-announcement-modal"')
        ->toContain('is-visible')
        ->toContain('Urgent System Maintenance')
        ->toContain('Please save your unsaved work immediately.')
        ->toContain('data-announcement-id="' . $announcement->id . '"')
        ->toContain('name="announcement_id" value="' . $announcement->id . '"');
});

it('renders hidden modal container when user has acknowledged the announcement', function (): void {
    // Create published announcement
    $announcement = $this->repository->create(
        title: 'Urgent System Maintenance',
        body: 'Please save your unsaved work.',
        status: 'published',
    );

    // User acknowledges
    $this->repository->acknowledge($announcement->id, $this->user->id);

    // Render layout for user
    $html = $this->layout->render(
        title: 'Dashboard',
        content: '<p>Main content area.</p>',
        user: $this->user,
    );

    expect($html)
        ->toContain('id="admin-announcement-modal"')
        ->not->toContain('is-visible')
        ->not->toContain('Urgent System Maintenance')
        ->toContain('hidden');
});
