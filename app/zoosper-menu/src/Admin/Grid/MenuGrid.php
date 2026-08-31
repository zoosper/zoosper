<?php

declare(strict_types=1);

namespace Zoosper\Menu\Admin\Grid;

use PDO;
use Zoosper\Pagination\PaginationResult;
use Zoosper\Core\Url\AdminUrlGenerator;
use Zoosper\Grid\{GridColumn, GridCriteria, GridDataSourceInterface, GridDefinition, GridFilter};

final readonly class MenuGrid implements GridDataSourceInterface
{
    public const KEY='admin.menus';

    public function __construct(
        private PDO $pdo,
        private AdminUrlGenerator $urls
    ) {
    }

    public function definition(): GridDefinition
    {
        return new GridDefinition(
            'Menus',
            [
                new GridColumn('id', 'ID', true, align: 'right', toggleable: false),
                new GridColumn('label', 'Label', true),
                new GridColumn('code', 'Code', true),
                new GridColumn('site_name', 'Site', true),
                new GridColumn('status', 'Status', true),
                new GridColumn('updated_at', 'Updated', true),
                new GridColumn('actions', 'Actions', toggleable: false, render: fn(mixed $v, array $r): string => '<a href="' . htmlspecialchars($this->urls->url('menus/' . (int)$r['id'] . '/edit'), ENT_QUOTES, 'UTF-8') . '">Edit</a>')
            ],
            [
                new GridFilter('q', 'Search'),
                new GridFilter('status', 'Status', 'select', [['value' => 'active', 'label' => 'Active'], ['value' => 'inactive', 'label' => 'Inactive']])
            ],
            'label',
            'asc',
            'No menus exist yet.'
        );
    }

    public function paginate(GridCriteria $c): PaginationResult
    {
        $w = ['1=1'];
        $p = [];
        $q = trim((string)($c->filters['q'] ?? ''));
        if ($q !== '') {
            $w[] = '(m.label LIKE :q OR m.code LIKE :q)';
            $p['q'] = '%' . $q . '%';
        }
        $v = (string)($c->filters['status'] ?? '');
        if (in_array($v, ['active', 'inactive'], true)) {
            $w[] = 'm.status=:status';
            $p['status'] = $v;
        }
        $base = ' FROM menus m JOIN sites s ON s.id=m.site_id WHERE ' . implode(' AND ', $w);
        $n = $this->pdo->prepare('SELECT COUNT(*)' . $base);
        $n->execute($p);
        $sort = ['id' => 'm.id', 'label' => 'm.label', 'code' => 'code', 'site_name' => 's.name', 'status' => 'm.status', 'updated_at' => 'm.updated_at'][$c->sortBy ?? 'label'] ?? 'm.label';
        $d = $c->sortDir === 'desc' ? 'DESC' : 'ASC';
        $st = $this->pdo->prepare('SELECT m.id, m.label, m.code, m.status, m.updated_at, s.name AS site_name' . $base . " ORDER BY $sort $d, m.id ASC LIMIT :limit OFFSET :offset");
        foreach ($p as $k => $x) {
            $st->bindValue(':' . $k, $x);
        }
        $st->bindValue(':limit', $c->pager->pageSize, PDO::PARAM_INT);
        $st->bindValue(':offset', ($c->pager->page - 1) * $c->pager->pageSize, PDO::PARAM_INT);
        $st->execute();

        return new PaginationResult($st->fetchAll(PDO::FETCH_ASSOC) ?: [], (int)$n->fetchColumn(), $c->pager->page, $c->pager->pageSize);
    }
}










