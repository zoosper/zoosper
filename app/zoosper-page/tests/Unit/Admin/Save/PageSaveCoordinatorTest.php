<?php

declare(strict_types=1);

use Zoosper\Auth\Model\AdminUser;
use Zoosper\Core\Html\BasicHtmlSanitizer;
use Zoosper\Page\Application\Save\PageSaveCoordinator;
use Zoosper\Page\Repository\PageRepository;

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
        ->and($repo->findById((int) $created->pageId)?->status)->toBe('published');
});

it('returns invalid Editor.js JSON as a typed failure', function (): void {
    $service = new PageSaveCoordinator(new PageRepository(phase9fnPagesPdo()), new BasicHtmlSanitizer());
    $result = $service->create([
        'site_id' => 1, 'title' => 'Bad', 'slug' => 'bad', 'content' => 'x',
        'content_json' => '{invalid',
    ], phase9fnUser());
    expect($result->successful)->toBeFalse()->and($result->error)->toContain('Invalid Editor.js JSON payload');
});










