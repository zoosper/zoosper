<?php

declare(strict_types=1);

use Zoosper\Auth\Model\AdminUser;
use Zoosper\Core\Entity\Save\EntitySaveEventDispatcher;
use Zoosper\Core\Entity\Save\EntitySaveLifecycleRunner;
use Zoosper\Core\Html\BasicHtmlSanitizer;
use Zoosper\Page\Application\Save\PageSaveCoordinator;
use Zoosper\Page\Application\Save\PageSaveInput;
use Zoosper\Page\Repository\PageRepository;
use Zoosper\Page\Repository\PageRevisionRepository;
use Zoosper\Page\Service\PageRevisionService;

function phase9fnPagesPdo(): PDO
{
    $pdo = new PDO('sqlite::memory:');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec('CREATE TABLE pages (id INTEGER PRIMARY KEY AUTOINCREMENT, site_id INTEGER NOT NULL, title TEXT NOT NULL, slug TEXT NOT NULL, content TEXT NOT NULL, status TEXT NOT NULL, created_by INTEGER, updated_by INTEGER, created_at TEXT, updated_at TEXT, published_at TEXT, content_format TEXT, content_json TEXT, meta_title TEXT, meta_description TEXT, meta_keywords TEXT, canonical_url TEXT)');
    return $pdo;
}

function phase9fnUser(): AdminUser
{
    $reflection = new ReflectionClass(AdminUser::class);
    $constructor = $reflection->getConstructor();
    $args = [];
    foreach ($constructor->getParameters() as $parameter) {
        $args[] = match ($parameter->getName()) {
            'id' => 7,
            'email' => 'admin@example.test',
            'name', 'firstName', 'lastName' => 'Admin',
            'passwordHash' => 'hash',
            'status' => 'active',
            'permissions' => ['page.manage'],
            'isActive' => true,
            default => $parameter->isDefaultValueAvailable() ? $parameter->getDefaultValue() : null,
        };
    }
    return $reflection->newInstanceArgs($args);
}

it('normalises and persists Page create and update through one coordinator', function (): void {
    $repo = new PageRepository(phase9fnPagesPdo());
    $service = new PageSaveCoordinator($repo, new BasicHtmlSanitizer());
    $user = phase9fnUser();
    $created = $service->create([
        'site_id' => 1, 'title' => '  Home  ', 'slug' => ' Hello World ',
        'content' => '<p>Body</p>', 'meta_title' => ' SEO ',
    ], $user);
    expect($created->successful)->toBeTrue();
    $page = $repo->findById((int) $created->pageId);
    expect($page?->title)->toBe('Home')->and($page?->slug)->toBe('hello-world')->and($page?->metaTitle)->toBe('SEO');

    $updated = $service->update([
        'site_id' => 1, 'title' => 'Updated', 'slug' => 'New Slug',
        'content' => 'Updated body', 'publish' => '1',
    ], $page, $user);
    expect($updated->successful)->toBeTrue()
        ->and($repo->findById((int) $created->pageId)?->status)->toBe('published')
        ->and($repo->findById((int) $created->pageId)?->slug)->toBe('new-slug');
});

it('does not publish an archived Page through the save form', function (): void {
    $repo = new PageRepository(phase9fnPagesPdo());
    $id = $repo->create(1, 'Archived', 'archived', 'Original', 'archived');
    $service = new PageSaveCoordinator($repo, new BasicHtmlSanitizer());
    $result = $service->update([
        'site_id' => 1, 'title' => 'Changed', 'slug' => 'changed',
        'content' => 'Changed', 'publish' => '1',
    ], $repo->findById($id), phase9fnUser());

    expect($result->successful)->toBeFalse()
        ->and($repo->findById($id)?->status)->toBe('archived')
        ->and($repo->findById($id)?->title)->toBe('Archived');
});

it('does not publish a Page archived after the save form loaded it', function (): void {
    $repo = new PageRepository(phase9fnPagesPdo());
    $id = $repo->create(1, 'Original', 'original', 'Original');
    $stale = $repo->findById($id);
    $repo->archive($id, 9);
    $service = new PageSaveCoordinator($repo, new BasicHtmlSanitizer());

    $result = $service->update([
        'site_id' => 1, 'title' => 'Changed', 'slug' => 'changed',
        'content' => 'Changed', 'publish' => '1',
    ], $stale, phase9fnUser());

    expect($result->successful)->toBeFalse()
        ->and($repo->findById($id)?->status)->toBe('archived');
});

it('captures created and pre-update revisions with direct and lifecycle persistence', function (bool $useLifecycle): void {
    $pdo = phase9fnPagesPdo();
    $pdo->exec('CREATE TABLE page_revisions (id INTEGER PRIMARY KEY AUTOINCREMENT, page_id INTEGER, title TEXT, slug TEXT, content TEXT, status TEXT, content_format TEXT, content_json TEXT, meta_title TEXT, meta_description TEXT, meta_keywords TEXT, canonical_url TEXT, created_by INTEGER, created_at TEXT)');
    $pages = new PageRepository($pdo);
    $revisions = new PageRevisionRepository($pdo);
    $service = new PageSaveCoordinator(
        $pages,
        new BasicHtmlSanitizer(),
        lifecycle: $useLifecycle ? new EntitySaveLifecycleRunner(new EntitySaveEventDispatcher()) : null,
        revisions: new PageRevisionService($revisions),
    );
    $user = phase9fnUser();
    $created = $service->create(['site_id' => 1, 'title' => 'Original', 'slug' => 'original', 'content' => 'Before'], $user);
    expect($created->successful)->toBeTrue();
    $page = $pages->findById($created->pageId);
    expect($page)->not->toBeNull();
    $updated = $service->update(['site_id' => 1, 'title' => 'Changed', 'slug' => 'changed', 'content' => 'After', 'publish' => '1'], $page, $user);

    expect($updated->successful)->toBeTrue()
        ->and($updated->pageId)->toBe($created->pageId)
        ->and($pages->findById($created->pageId)?->status)->toBe('published')
        ->and(array_map(static fn ($revision) => $revision->title, $revisions->forPage($created->pageId)))->toBe(['Original', 'Original']);
})->with([false, true]);

it('preserves slug normalization boundaries', function (array $form, string $expected): void {
    expect(PageSaveInput::fromForm($form, new BasicHtmlSanitizer())->slug)->toBe($expected);
})->with([
    'missing' => [[], ''],
    'empty' => [['slug' => ''], ''],
    'string zero' => [['slug' => '0'], ''],
    'integer zero' => [['slug' => 0], ''],
    'surrounded zero' => [['slug' => '-0-'], '0'],
    'punctuation' => [['slug' => '!!!'], ''],
    'mixed case' => [['slug' => ' Hello World! '], 'hello-world'],
]);

it('returns invalid Editor.js JSON as a typed failure', function (): void {
    $service = new PageSaveCoordinator(new PageRepository(phase9fnPagesPdo()), new BasicHtmlSanitizer());
    $result = $service->create([
        'site_id' => 1, 'title' => 'Bad', 'slug' => 'bad', 'content' => 'x',
        'content_json' => '{invalid',
    ], phase9fnUser());
    expect($result->successful)->toBeFalse()->and($result->error)->toContain('Invalid Editor.js JSON payload');
});










