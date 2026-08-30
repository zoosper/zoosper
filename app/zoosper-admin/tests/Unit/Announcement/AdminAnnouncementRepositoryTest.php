<?php

declare(strict_types=1);

namespace Zoosper\Admin\Tests\Unit\Announcement;

use PDO;
use Zoosper\Admin\Announcement\AdminAnnouncementRepository;

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

it('creates, reads, updates, publishes, unpublishes, and archives announcements', function (): void {
    $created = $this->repository->create(
        title: 'Maintenance Notice',
        body: 'System will be offline on Sunday for 15 minutes.',
        status: 'draft',
        createdByUserId: 1,
    );

    expect($created->id)->toBeGreaterThan(0);
    expect($created->title)->toBe('Maintenance Notice');
    expect($created->body)->toBe('System will be offline on Sunday for 15 minutes.');
    expect($created->isDraft())->toBeTrue();
    expect($created->isPublished())->toBeFalse();

    $found = $this->repository->findById($created->id);
    expect($found)->not->toBeNull();
    expect($found->title)->toBe('Maintenance Notice');

    // Update announcement
    $this->repository->update(
        id: $created->id,
        title: 'Updated Notice',
        body: 'Maintenance moved to Monday.',
        status: 'draft',
    );
    $updated = $this->repository->findById($created->id);
    expect($updated->title)->toBe('Updated Notice');
    expect($updated->body)->toBe('Maintenance moved to Monday.');

    // Publish announcement
    $this->repository->publish($created->id);
    $published = $this->repository->findById($created->id);
    expect($published->isPublished())->toBeTrue();
    expect($published->publishedAt)->not->toBeNull();

    // Unpublish announcement
    $this->repository->unpublish($created->id);
    $unpublished = $this->repository->findById($created->id);
    expect($unpublished->isDraft())->toBeTrue();

    // Archive announcement
    $this->repository->archive($created->id);
    $archived = $this->repository->findById($created->id);
    expect($archived->isArchived())->toBeTrue();
});

it('identifies unacknowledged published announcements per user and records acknowledgments', function (): void {
    $user1Id = 10;
    $user2Id = 20;

    // No published announcements yet
    expect($this->repository->findUnacknowledgedForUser($user1Id))->toBeNull();

    // Create a draft announcement - should not be returned
    $draft = $this->repository->create('Draft Item', 'Not yet live', 'draft');
    expect($this->repository->findUnacknowledgedForUser($user1Id))->toBeNull();

    // Create and publish an announcement
    $announcement = $this->repository->create('Global Update', 'Please review new policy', 'published');

    // Both users should see it as unacknowledged
    $unack1 = $this->repository->findUnacknowledgedForUser($user1Id);
    expect($unack1)->not->toBeNull();
    expect($unack1->id)->toBe($announcement->id);
    expect($unack1->title)->toBe('Global Update');

    $unack2 = $this->repository->findUnacknowledgedForUser($user2Id);
    expect($unack2)->not->toBeNull();
    expect($unack2->id)->toBe($announcement->id);

    // User 1 acknowledges the announcement
    expect($this->repository->isAcknowledged($announcement->id, $user1Id))->toBeFalse();
    $this->repository->acknowledge($announcement->id, $user1Id);
    expect($this->repository->isAcknowledged($announcement->id, $user1Id))->toBeTrue();

    // Idempotent duplicate acknowledgment check
    $this->repository->acknowledge($announcement->id, $user1Id);
    expect($this->repository->countAcknowledgments($announcement->id))->toBe(1);

    // User 1 should no longer receive it
    expect($this->repository->findUnacknowledgedForUser($user1Id))->toBeNull();

    // User 2 should STILL receive it
    expect($this->repository->findUnacknowledgedForUser($user2Id))->not->toBeNull();

    // User 2 acknowledges
    $this->repository->acknowledge($announcement->id, $user2Id);
    expect($this->repository->countAcknowledgments($announcement->id))->toBe(2);
    expect($this->repository->findUnacknowledgedForUser($user2Id))->toBeNull();

    // Counts map verification
    $counts = $this->repository->acknowledgmentCounts();
    expect($counts[$announcement->id] ?? 0)->toBe(2);
});
