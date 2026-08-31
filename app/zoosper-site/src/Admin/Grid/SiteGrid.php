<?php

declare(strict_types=1);

namespace Zoosper\Site\Admin\Grid;

use PDO;
use Zoosper\Pagination\PaginationResult;
use Zoosper\Core\Url\AdminUrlGenerator;
use Zoosper\Grid\{
    GridColumn,
    GridCriteria,
    GridDataSourceInterface,
    GridDefinition,
    GridFilter
};

final readonly class SiteGrid implements GridDataSourceInterface
{
    public const KEY='admin.sites';

    public function __construct(
        private PDO $pdo,
        private AdminUrlGenerator $urls
    ) {
    }

    public function definition(): GridDefinition
    {
        return new GridDefinition(
            'Sites',
            [
                new GridColumn('id', 'ID', true, align: 'right', toggleable: false),
                new GridColumn('name', 'Name', true),
                new GridColumn('code', 'Code', true),
                new GridColumn('status', 'Status', true),
                new GridColumn('locale', 'Locale'),
                new GridColumn('theme_code', 'Theme'),
                new GridColumn('actions', 'Actions', toggleable: false, render: fn(mixed $v, array $r): string => '<a href="' . htmlspecialchars($this->urls->url('sites/' . (int)$r['id'] . '/edit'), ENT_QUOTES, 'UTF-8') . '">Edit</a>')
            ],
            [
                new GridFilter('q', 'Search'),
                new GridFilter('status', 'Status', 'select', [['value' => 'active', 'label' => 'Active'], ['value' => 'inactive', 'label' => 'Inactive']])
            ],
            'name',
            'asc',
            'No sites exist yet.'
        );
    }

    public function paginate(GridCriteria $c): PaginationResult
    {
        $w = ['1=1'];
        $p = [];
        $q = trim((string)($c->filters['q'] ?? ''));

        if ($q !== '') {
            $w[] = '(name LIKE :q OR code LIKE :q)';
            $p['q'] = '%' . $q . '%';
        }

        $v = (string)($c->filters['status'] ?? '');
        if (in_array($v, ['active', 'inactive'], true)) {
            $w[] = 'status=:status';
            $p['status'] = $v;
        }

        $base = ' FROM sites WHERE ' . implode(' AND ', $w);
        $n = $this->pdo->prepare('SELECT COUNT(*)' . $base);
        $n->execute($p);

        $sort = ['id' => 'id', 'name' => 'name', 'code' => 'code', 'status' => 'status'][$c->sortBy ?? 'name'] ?? 'name';

        return $this->page($c, $p, $base, $n, $sort);
    }

    private function page(GridCriteria $c, array $p, string $base, \PDOStatement $n, string $sort): PaginationResult
    {
        $d = $c->sortDir === 'desc' ? 'DESC' : 'ASC';
        $s = $this->pdo->prepare('SELECT id, name, code, status, locale, theme_code' . $base . " ORDER BY $sort $d, id ASC LIMIT :limit OFFSET :offset");

        foreach ($p as $k => $v) {
            $s->bindValue(':' . $k, $v);
        }

        $s->bindValue(':limit', $c->pager->pageSize, PDO::PARAM_INT);
        $s->bindValue(':offset', ($c->pager->page - 1) * $c->pager->pageSize, PDO::PARAM_INT);
        $s->execute();

        return new PaginationResult($s->fetchAll(PDO::FETCH_ASSOC) ?: [], (int)$n->fetchColumn(), $c->pager->page, $c->pager->pageSize);
    }
}










