<?php

declare(strict_types=1);

namespace Zoosper\Page\Tests\Unit\Admin;

use PDO;
use ReflectionMethod;
use Zoosper\AdminGrid\GridBookmarkRepository;
use Zoosper\AdminGrid\GridPreferenceRepository;
use Zoosper\AdminGrid\GridStateNormaliser;
use Zoosper\AdminGrid\GridViewMutationService;
use Zoosper\AdminGrid\GridWorkspaceMutationContract;
use Zoosper\Page\Admin\PageGridDefinition;
use Zoosper\Page\Admin\PageGridMutationHandler;
use Zoosper\Page\Admin\PageGridWorkspace;

test('Pages mutation handler requires explicit authenticated admin identity', function (): void {
    $method = new ReflectionMethod(PageGridMutationHandler::class, 'handle');
    $parameters = $method->getParameters();

    expect($parameters[0]->getName())->toBe('adminUserId');
    expect((string) $parameters[0]->getType())->toBe('int');
    expect($parameters[1]->getName())->toBe('action');
    expect((string) $parameters[1]->getType())->toBe('string');
});

test('saving a page view only makes it default when requested', function (): void {
    $pdo = new PDO('sqlite::memory:');
    $pdo->exec('CREATE TABLE admin_grid_bookmarks (id INTEGER PRIMARY KEY AUTOINCREMENT, admin_user_id INTEGER NOT NULL, grid_key TEXT NOT NULL, name TEXT NOT NULL, state_json TEXT NOT NULL, is_default INTEGER NOT NULL DEFAULT 0, created_at TEXT NOT NULL, updated_at TEXT NOT NULL)');
    $pdo->exec('CREATE UNIQUE INDEX bookmarks_user_grid_name ON admin_grid_bookmarks(admin_user_id, grid_key, name)');
    $bookmarks = new GridBookmarkRepository($pdo);
    $handler = new PageGridMutationHandler(
        new PageGridDefinition(),
        new GridViewMutationService(new GridPreferenceRepository($pdo), $bookmarks, new GridStateNormaliser()),
    );

    $handler->handle(10, GridWorkspaceMutationContract::SAVE_VIEW, ['view_name' => 'Ordinary']);
    $handler->handle(10, GridWorkspaceMutationContract::SET_DEFAULT_VIEW, ['view_name' => 'Default']);

    $rows = $bookmarks->allForUser(10, PageGridWorkspace::GRID_KEY);
    $defaults = [];
    foreach ($rows as $row) {
        $defaults[$row['name']] = (bool) $row['is_default'];
    }
    expect($defaults)->toBe(['Default' => true, 'Ordinary' => false])
        ->and($bookmarks->allForUser(11, PageGridWorkspace::GRID_KEY))->toBe([]);
});










