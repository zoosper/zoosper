<?php

declare(strict_types=1);

use Zoosper\Page\Repository\PageRepository;

/*
 * Behavioural coverage that was missing: construct PageRepository against a REAL
 * SQLite PDO and exercise create/find. Prior to Phase 1.94 the constructor used
 * MySQL-only INFORMATION_SCHEMA queries and fataled here.
 */

function makePagesSqlitePdo(bool $withOptionalColumns = true): PDO
{
    $pdo = new PDO('sqlite::memory:');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $optional = $withOptionalColumns
        ? ', content_format TEXT, content_json TEXT, meta_title TEXT, meta_description TEXT, meta_keywords TEXT, canonical_url TEXT'
        : '';

    $pdo->exec(
        'CREATE TABLE pages (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            site_id INTEGER NOT NULL,
            title TEXT NOT NULL,
            slug TEXT NOT NULL,
            content TEXT NOT NULL,
            status TEXT NOT NULL DEFAULT "draft",
            created_by INTEGER,
            updated_by INTEGER,
            created_at TEXT,
            updated_at TEXT,
            published_at TEXT' . $optional . '
        )'
    );

    return $pdo;
}

it('constructs against sqlite without INFORMATION_SCHEMA and detects optional columns', function (): void {
    $pdo = makePagesSqlitePdo(withOptionalColumns: true);

    // Prior to the fix this threw: "no such table: INFORMATION_SCHEMA.COLUMNS".
    $repo = new PageRepository($pdo);

    $id = $repo->create(
        siteId: 1,
        title: 'Home',
        slug: 'home',
        content: '<p>hello</p>',
        status: 'published',
        contentFormat: 'html',
        metaTitle: 'Home | Zoosper',
    );

    expect($id)->toBeGreaterThan(0);

    $page = $repo->findById($id);
    expect($page)->not->toBeNull()
        ->and($page->title)->toBe('Home')
        ->and($page->status)->toBe('published')
        ->and($page->metaTitle)->toBe('Home | Zoosper');
});

it('works on a minimal sqlite schema missing the optional columns', function (): void {
    $pdo = makePagesSqlitePdo(withOptionalColumns: false);

    $repo = new PageRepository($pdo);
    $id = $repo->create(siteId: 1, title: 'Bare', slug: 'bare', content: 'x');

    $page = $repo->findById($id);
    expect($page)->not->toBeNull()
        ->and($page->title)->toBe('Bare')
        // Missing column falls back to the model default.
        ->and($page->contentFormat)->toBe('html')
        ->and($page->metaTitle)->toBeNull();
});

it('finds a published page by slug on sqlite', function (): void {
    $pdo = makePagesSqlitePdo();
    $repo = new PageRepository($pdo);
    $repo->create(siteId: 7, title: 'About', slug: 'about', content: 'a', status: 'published');

    $page = $repo->findPublishedBySlug(7, 'about');
    expect($page)->not->toBeNull()->and($page->slug)->toBe('about');

    expect($repo->findPublishedBySlug(7, 'missing'))->toBeNull();
});










