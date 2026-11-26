<?php

declare(strict_types=1);

use Zoosper\Core\Config\ConfigRepository;
use Zoosper\Core\Url\AdminUrlGenerator;
use Zoosper\Grid\GridCriteria;
use Zoosper\Menu\Admin\Grid\MenuGrid;
use Zoosper\Pagination\Pager;
use Zoosper\Site\Admin\Grid\SiteGrid;

final class NativePlaceholderCheckingPdo extends PDO
{
    public array $queries = [];

    public function prepare(string $query, array $options = []): PDOStatement|false
    {
        $this->queries[] = $query;
        preg_match_all('/:([a-z_]+)/', $query, $matches);
        if (count($matches[1]) !== count(array_unique($matches[1]))) {
            throw new PDOException('Repeated named placeholder in native prepared statement');
        }

        return parent::prepare($query, $options);
    }
}

it('searches menu and site names and codes with native-compatible placeholders', function (): void {
    $pdo = new NativePlaceholderCheckingPdo('sqlite::memory:');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec('CREATE TABLE sites (id INTEGER PRIMARY KEY, name TEXT, code TEXT, status TEXT, locale TEXT, theme_code TEXT)');
    $pdo->exec('CREATE TABLE menus (id INTEGER PRIMARY KEY, site_id INTEGER, label TEXT, code TEXT, status TEXT, updated_at TEXT)');
    $pdo->exec("INSERT INTO sites VALUES (1, 'Alpha', 'site-code', 'active', 'en', 'default'), (2, 'Other', 'beta-code', 'inactive', 'en', 'default')");
    $pdo->exec("INSERT INTO menus VALUES (1, 1, 'First', 'menu-code', 'active', '2026-01-01'), (2, 1, 'Second', 'beta-menu', 'inactive', '2026-01-01')");
    $urls = new AdminUrlGenerator(ConfigRepository::fromArray([]));

    foreach ([new MenuGrid($pdo, $urls), new SiteGrid($pdo, $urls)] as $grid) {
        foreach (['code', $grid instanceof MenuGrid ? 'first' : 'alpha'] as $term) {
            $result = $grid->paginate(new GridCriteria(new Pager(1, 20), 'code', 'asc', ['q' => $term, 'status' => 'active']));
            expect($result->total)->toBe(1)
                ->and($result->items)->toHaveCount(1);
        }
        $result = $grid->paginate(new GridCriteria(new Pager(1, 20), 'code', 'asc', ['q' => 'beta', 'status' => 'active']));
        expect($result->total)->toBe(0);
    }

    expect(implode("\n", $pdo->queries))->toContain('ORDER BY m.code ASC');
});