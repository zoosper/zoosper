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

final readonly class SiteDomainGrid implements GridDataSourceInterface
{
    public const KEY = 'admin.site-domains';

    public function __construct(
        private PDO $pdo,
        private AdminUrlGenerator $urls
    ) {
    }

    public function definition(): GridDefinition
    {
        return new GridDefinition(
            'Site Domains',
            [
                new GridColumn('id', 'ID', true, align: 'right', toggleable: false),
                new GridColumn('host', 'Host', true),
                new GridColumn('site_name', 'Site', true),
                new GridColumn('is_primary', 'Primary', true, render: fn($v) => $v ? 'Yes' : 'No'),
                new GridColumn('actions', 'Actions', toggleable: false, render: fn(mixed $v, array $r): string => '<a href="' . htmlspecialchars($this->urls->url('site-domains/edit', ['id' => (int)$r['id']]), ENT_QUOTES, 'UTF-8') . '">Edit</a>')
            ],
            [
                new GridFilter('q', 'Host'),
                new GridFilter('primary', 'Primary', 'select', [['value' => 'yes', 'label' => 'Yes'], ['value' => 'no', 'label' => 'No']])
            ],
            'host',
            'asc',
            'No site domains exist yet.'
        );
    }

    public function paginate(GridCriteria $c): PaginationResult
    {
        $w = ['1=1'];
        $p = [];
        $q = trim((string)($c->filters['q'] ?? ''));

        if ($q !== '') {
            $w[] = 'd.host LIKE :q';
            $p['q'] = '%' . $q . '%';
        }

        $v = (string)($c->filters['primary'] ?? '');
        if (in_array($v, ['yes', 'no'], true)) {
            $w[] = 'd.is_primary=:primary';
            $p['primary'] = $v === 'yes' ? 1 : 0;
        }

        $base = ' FROM site_domains d JOIN sites s ON s.id=d.site_id WHERE ' . implode(' AND ', $w);
        $n = $this->pdo->prepare('SELECT COUNT(*)' . $base);
        $n->execute($p);

        $sort = ['id' => 'd.id', 'host' => 'd.host', 'site_name' => 's.name', 'is_primary' => 'd.is_primary'][$c->sortBy ?? 'host'] ?? 'd.host';
        $d = $c->sortDir === 'desc' ? 'DESC' : 'ASC';
        $st = $this->pdo->prepare('SELECT d.id, d.host, d.is_primary, s.name AS site_name' . $base . " ORDER BY $sort $d, d.id ASC LIMIT :limit OFFSET :offset");

        foreach ($p as $k => $x) {
            $st->bindValue(':' . $k, $x);
        }

        $st->bindValue(':limit', $c->pager->pageSize, PDO::PARAM_INT);
        $st->bindValue(':offset', ($c->pager->page - 1) * $c->pager->pageSize, PDO::PARAM_INT);
        $st->execute();

        return new PaginationResult($st->fetchAll(PDO::FETCH_ASSOC) ?: [], (int)$n->fetchColumn(), $c->pager->page, $c->pager->pageSize);
    }
}
