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
