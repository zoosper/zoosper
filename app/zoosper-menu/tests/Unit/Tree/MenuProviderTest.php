<?php
declare(strict_types=1);

use Zoosper\Menu\Contract\MenuItemRepositoryInterface;
use Zoosper\Menu\Contract\MenuRepositoryInterface;
use Zoosper\Menu\Model\Menu;
use Zoosper\Menu\Model\MenuItem;
use Zoosper\Menu\Service\BreadcrumbProvider;
use Zoosper\Menu\Service\MenuProvider;
use Zoosper\Menu\Service\MenuTreeBuilder;

it('resolves a nested menu and returns the first matching breadcrumb trail', function () {
    $pdo = new PDO('sqlite::memory:');
    $pdo->exec('CREATE TABLE pages (id INTEGER, site_id INTEGER, slug TEXT, status TEXT)');
    $pdo->exec("INSERT INTO pages VALUES (10, 1, 'about', 'published'), (11, 1, 'draft', 'draft'), (12, 2, 'other-site', 'published')");
    $menus = new class implements MenuRepositoryInterface {
        public function findActiveBySiteAndCode(int $siteId, string $code): ?Menu
        {
            return $siteId === 1 && $code === 'main' ? new Menu(1, 1, 'main', 'Main', 'active', '', '') : null;
        }
    };
    $items = new class implements MenuItemRepositoryInterface {
        public function activeForMenu(int $menuId): array
        {
            return [
                new MenuItem(1, 1, null, null, 'Home', '/', '_self', 1, 'active'),
                new MenuItem(2, 1, null, 10, 'About', null, '_self', 2, 'active'),
                new MenuItem(3, 1, 2, null, 'Team', '/about/team', '_self', 1, 'active'),
                new MenuItem(4, 1, 2, 11, 'Draft', null, '_self', 2, 'active'),
                new MenuItem(5, 1, 2, 12, 'Other site', null, '_self', 3, 'active'),
                new MenuItem(6, 1, null, null, 'Duplicate', '/about/team', '_self', 3, 'active'),
            ];
        }

        public function wouldCreateCycle(int $menuId, int $itemId, ?int $parentId): bool { return false; }
    };
    $provider = new MenuProvider($menus, $items, new MenuTreeBuilder(), $pdo);
    $tree = $provider->tree(1, 'main', '/about/team/');

    expect(array_is_list($tree))->toBeTrue()
        ->and(array_is_list($tree[1]->children))->toBeTrue()
        ->and($tree[1]->href)->toBe('/about')
        ->and($tree[1]->children[0]->active)->toBeTrue()
        ->and($tree[1]->children[1]->href)->toBe('#')
        ->and($tree[1]->children[2]->href)->toBe('#');

    $breadcrumbs = new BreadcrumbProvider($provider);
    expect($breadcrumbs->breadcrumbs(1, 'main', '/about/team/'))->toBe([
        ['label' => 'About', 'href' => '/about'],
        ['label' => 'Team', 'href' => '/about/team'],
    ])->and($breadcrumbs->breadcrumbs(1, 'main', '/missing'))->toBe([])
        ->and($breadcrumbs->breadcrumbs(1, 'missing', '/'))->toBe([])
        ->and($provider->tree(1, 'missing'))->toBe([]);
});

it('still rejects a tree that exceeds the maximum depth', function () {
    $items = [
        new MenuItem(1, 1, null, null, 'Parent', '/parent', '_self', 1, 'active'),
        new MenuItem(2, 1, 1, null, 'Child', '/child', '_self', 1, 'active'),
    ];

    expect(fn () => (new MenuTreeBuilder())->build($items, [], '/', 1))
        ->toThrow(RuntimeException::class, 'Menu tree exceeds maximum depth.');
});