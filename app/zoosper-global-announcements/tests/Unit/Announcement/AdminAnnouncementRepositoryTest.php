<?php

declare(strict_types=1);

namespace Zoosper\GlobalAnnouncements\Tests\Unit\Announcement;

use PDO;
use Zoosper\Core\Announcement\AdminAnnouncementProviderInterface;
use Zoosper\GlobalAnnouncements\Announcement\AdminAnnouncement;
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
});

it('implements Core AdminAnnouncementProviderInterface contract', function (): void {
    expect($this->repository)->toBeInstanceOf(AdminAnnouncementProviderInterface::class);
});

it('creates, reads, updates and transitions announcement lifecycle states', function (): void {
    $announcement = $this->repository->create(
        title: 'Security Update Required',
        body: 'Please rotate your keys.',
        status: 'draft',
        createdByUserId: 1,
    );

    expect($announcement)
        ->toBeInstanceOf(AdminAnnouncement::class)
        ->and($announcement->title)->toBe('Security Update Required')
        ->and($announcement->body)->toBe('Please rotate your keys.')
        ->and($announcement->isDraft())->toBeTrue()
        ->and($announcement->isPublished())->toBeFalse()
        ->and($announcement->isArchived())->toBeFalse()
        ->and($announcement->publishedAt)->toBeNull();

    // Publish
    $this->repository->publish($announcement->id);
    $published = $this->repository->findById($announcement->id);
    expect($published)
        ->not->toBeNull()
        ->and($published->isPublished())->toBeTrue()
        ->and($published->publishedAt)->not->toBeNull();

    // Unpublish
    $this->repository->unpublish($announcement->id);
    $unpublished = $this->repository->findById($announcement->id);
    expect($unpublished)
        ->not->toBeNull()
        ->and($unpublished->isDraft())->toBeTrue();

    // Archive
    $this->repository->archive($announcement->id);
    $archived = $this->repository->findById($announcement->id);
    expect($archived)
        ->not->toBeNull()
        ->and($archived->isArchived())->toBeTrue();
});

it('detects unacknowledged announcements for specific users and records duplicate-safe acknowledgments', function (): void {
    $announcement = $this->repository->create(
        title: 'Scheduled Outage Tonight',
        body: 'Outage starting at 23:00 UTC.',
        status: 'published',
    );

    // User 10 has not acknowledged
    $pending = $this->repository->findUnacknowledgedForUser(10);
    expect($pending)->not->toBeNull()
        ->and($pending->id)->toBe($announcement->id);

    // User 10 acknowledges
    $this->repository->acknowledge($announcement->id, 10);
    expect($this->repository->isAcknowledged($announcement->id, 10))->toBeTrue();

    // Now no unacknowledged announcement for user 10
    $pendingAfter = $this->repository->findUnacknowledgedForUser(10);
    expect($pendingAfter)->toBeNull();

    // User 20 still sees it
    $pendingUser20 = $this->repository->findUnacknowledgedForUser(20);
    expect($pendingUser20)->not->toBeNull()
        ->and($pendingUser20->id)->toBe($announcement->id);

    // Duplicate acknowledgment should not throw
    $this->repository->acknowledge($announcement->id, 10);
    $counts = $this->repository->acknowledgmentCounts();
    expect($counts[$announcement->id] ?? 0)->toBe(1);
});










