<?php

declare(strict_types=1);

namespace Zoosper\Page\Tests\Unit\Admin;

use PDO;
use Zoosper\Grid\BulkAction\GridBulkActionDefinition;
use Zoosper\Grid\BulkAction\GridBulkActor;
use Zoosper\Grid\BulkAction\GridBulkConfirmationPolicy;
use Zoosper\Grid\BulkAction\GridBulkExecutionContext;
use Zoosper\Grid\BulkAction\GridBulkExecutionType;
use Zoosper\Grid\BulkAction\GridBulkSelection;
use Zoosper\Grid\BulkAction\GridBulkSelectionScope;
use Zoosper\Page\Admin\BulkAction\PagePublishSelectedExecutor;
use Zoosper\Page\Admin\BulkAction\PagePublishSideEffectsInterface;
use Zoosper\Page\Application\Publication\PagePublicationCoordinator;
use Zoosper\Page\Model\Page;
use Zoosper\Page\Repository\PageRepository;

function publishExecutorPdo(): PDO
{
    $pdo = new PDO('sqlite::memory:');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec('CREATE TABLE pages (id INTEGER PRIMARY KEY AUTOINCREMENT, site_id INTEGER NOT NULL, title TEXT NOT NULL, slug TEXT NOT NULL, content TEXT NOT NULL, status TEXT NOT NULL, created_by INTEGER, updated_by INTEGER, created_at TEXT, updated_at TEXT, published_at TEXT)');
    return $pdo;
}

function publishDefinition(): GridBulkActionDefinition
{
    return new GridBulkActionDefinition(
        id: PagePublishSelectedExecutor::ACTION_ID,
        label: 'Publish selected',
        selectionScope: GridBulkSelectionScope::EXPLICIT_IDENTITIES,
        executionType: GridBulkExecutionType::SERVER_MUTATION,
        confirmationPolicy: GridBulkConfirmationPolicy::CONFIRM,
        requiredPermission: 'page.manage',
        maximumSelection: 100,
        auditRequired: true,
    );
}

it('preflights all identities before publishing and reports published and skipped counts', function (): void {
    $pdo = publishExecutorPdo();
    $repository = new PageRepository($pdo);
    $draft = $repository->create(1, 'Draft', 'draft', 'Content');
    $published = $repository->create(1, 'Live', 'live', 'Content', 'published');
    $effects = new class implements PagePublishSideEffectsInterface {
        /** @var list<int> */ public array $pageIds = [];
        public function afterPublished(Page $page, GridBulkExecutionContext $context, int $selectedCount): void
        { $this->pageIds[] = $page->id; }
    };
    $result = (new PagePublishSelectedExecutor($repository, $effects))->execute(
        publishDefinition(),
        new GridBulkSelection([$draft, $published], 100),
        new GridBulkExecutionContext(new GridBulkActor(7, 'admin@example.test')),
    );
    expect($result->context)->toMatchArray([
        'selected' => 2, 'published' => 1, 'skipped_already_published' => 1,
    ])->and($repository->findById($draft)?->isPublished())->toBeTrue()
      ->and($repository->findById($draft)?->updatedBy)->toBe(7)
      ->and($effects->pageIds)->toBe([$draft]);
});

it('rejects a missing Page before mutating any selected Page', function (): void {
    $pdo = publishExecutorPdo();
    $repository = new PageRepository($pdo);
    $draft = $repository->create(1, 'Draft', 'draft', 'Content');
    $effects = new class implements PagePublishSideEffectsInterface {
        public int $calls = 0;
        public function afterPublished(Page $page, GridBulkExecutionContext $context, int $selectedCount): void { $this->calls++; }
    };
    expect(fn () => (new PagePublishSelectedExecutor($repository, $effects))->execute(
        publishDefinition(), new GridBulkSelection([$draft, 999], 100),
        new GridBulkExecutionContext(new GridBulkActor(7)),
    ))->toThrow(\InvalidArgumentException::class, 'not found');
    expect($repository->findById($draft)?->isPublished())->toBeFalse()->and($effects->calls)->toBe(0);
});

it('rejects archived Pages before publishing any selection', function (): void {
    $repository = new PageRepository(publishExecutorPdo());
    $draft = $repository->create(1, 'Draft', 'draft', 'Content');
    $archived = $repository->create(1, 'Archived', 'archived', 'Content', 'archived');
    $effects = new class implements PagePublishSideEffectsInterface {
        public int $calls = 0;
        public function afterPublished(Page $page, GridBulkExecutionContext $context, int $selectedCount): void { $this->calls++; }
    };

    expect(fn () => (new PagePublishSelectedExecutor($repository, $effects))->execute(
        publishDefinition(), new GridBulkSelection([$draft, $archived], 100),
        new GridBulkExecutionContext(new GridBulkActor(7)),
    ))->toThrow(\InvalidArgumentException::class, 'archived');
    expect($repository->findById($draft)?->status)->toBe('draft')
        ->and($repository->findById($archived)?->status)->toBe('archived')
        ->and($effects->calls)->toBe(0);
});

it('records a publication time when a Page is created published', function (): void {
    $repository = new PageRepository(publishExecutorPdo());
    $published = $repository->createPublished(1, 'Live', 'live', 'Content');
    $draft = $repository->create(1, 'Draft', 'draft', 'Content');

    expect($repository->findById($published)?->publishedAt)->not->toBeNull()
        ->and($repository->findById($draft)?->publishedAt)->toBeNull();
});

it('rejects single-page publication transitions for archived Pages', function (bool $publish): void {
    $repository = new PageRepository(publishExecutorPdo());
    $id = $repository->create(1, 'Archived', 'archived', 'Content', 'archived');
    $page = $repository->findById($id);
    $service = new PagePublicationCoordinator($repository);

    expect(fn () => $publish ? $service->publish($page, 7) : $service->unpublish($page, 7))
        ->toThrow(\RuntimeException::class, 'Archived');
    expect($repository->findById($id)?->status)->toBe('archived');
})->with([true, false]);

it('does not republish or emit side effects for already-published Pages', function (): void {
    $pdo = publishExecutorPdo();
    $repository = new PageRepository($pdo);
    $live = $repository->create(1, 'Live', 'live', 'Content', 'published');
    $effects = new class implements PagePublishSideEffectsInterface {
        public int $calls = 0;
        public function afterPublished(Page $page, GridBulkExecutionContext $context, int $selectedCount): void { $this->calls++; }
    };
    $result = (new PagePublishSelectedExecutor($repository, $effects))->execute(
        publishDefinition(), new GridBulkSelection([$live], 100),
        new GridBulkExecutionContext(new GridBulkActor(7)),
    );
    expect($result->context['published'])->toBe(0)
        ->and($result->context['skipped_already_published'])->toBe(1)
        ->and($effects->calls)->toBe(0);
});










