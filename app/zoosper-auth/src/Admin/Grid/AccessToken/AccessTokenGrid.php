<?php

declare(strict_types=1);

namespace Zoosper\Auth\Admin\Grid\AccessToken;

use PDO;
use Zoosper\Core\Url\AdminUrlGenerator;
use Zoosper\Grid\GridColumn;
use Zoosper\Grid\GridCriteria;
use Zoosper\Grid\GridDataSourceInterface;
use Zoosper\Grid\GridDefinition;
use Zoosper\Grid\GridFilter;
use Zoosper\Pagination\PaginationResult;

final readonly class AccessTokenGrid implements GridDataSourceInterface
{
    public const KEY='admin.access-tokens';

    public function __construct(
        private PDO $pdo,
        private int $ownerId,
        private AdminUrlGenerator $urls,
        private string $csrf,
    ) {
    }

    public function definition(): GridDefinition
    {
        return new GridDefinition(
            'Personal Access Tokens',
            [
                new GridColumn('name', 'Name', true, render: $this->renderName(...)),
                new GridColumn('scopes', 'Scopes', render: $this->renderScopes(...)),
                new GridColumn('expires_at', 'Expires', true, render: $this->renderDate(...)),
                new GridColumn('last_used_at', 'Last used', true, render: $this->renderDate(...)),
                new GridColumn('status', 'Status', render: $this->renderStatus(...)),
                new GridColumn('actions', 'Actions', toggleable: false, render: $this->renderActions(...)),
            ],
            [
                new GridFilter('q', 'Search'),
                new GridFilter('status', 'Status', 'select', [
                    ['value' => 'active', 'label' => 'Active'],
                    ['value' => 'expired', 'label' => 'Expired'],
                    ['value' => 'revoked', 'label' => 'Revoked'],
                ]),
            ],
            'created_at',
            'desc',
            'No Personal Access Tokens have been created.',
        );
    }

    public function paginate(GridCriteria $criteria): PaginationResult
    {
        $where = ['admin_user_id=:owner'];
        $parameters = ['owner' => $this->ownerId];
        $query = trim((string) ($criteria->filters['q'] ?? ''));
        if ($query !== '') {
            $where[] = 'name LIKE :q';
            $parameters['q'] = '%' . $query . '%';
        }

        $status = (string) ($criteria->filters['status'] ?? '');
        $now = gmdate('Y-m-d H:i:s');
        if ($status === 'revoked') {
            $where[] = 'revoked_at IS NOT NULL';
        } elseif ($status === 'expired') {
            $where[] = 'revoked_at IS NULL';
            $where[] = 'expires_at IS NOT NULL AND expires_at<=:now';
            $parameters['now'] = $now;
        } elseif ($status === 'active') {
            $where[] = 'revoked_at IS NULL';
            $where[] = '(expires_at IS NULL OR expires_at>:now)';
            $parameters['now'] = $now;
        }

        $base = ' FROM personal_access_tokens WHERE ' . implode(' AND ', $where);
        $count = $this->pdo->prepare('SELECT COUNT(*)' . $base);
        $count->execute($parameters);
        $sort = [
            'name' => 'name',
            'expires_at' => 'expires_at',
            'last_used_at' => 'last_used_at',
            'created_at' => 'created_at',
        ][$criteria->sortBy ?? 'created_at'] ?? 'created_at';
        $direction = $criteria->sortDir === 'asc' ? 'ASC' : 'DESC';
        $statement = $this->pdo->prepare(
            'SELECT id,name,scopes_json AS scopes,expires_at,last_used_at,revoked_at,created_at,'
            . "CASE WHEN revoked_at IS NOT NULL THEN 'Revoked' "
            . "WHEN expires_at IS NOT NULL AND expires_at<=:status_now THEN 'Expired' "
            . "ELSE 'Active' END AS status"
            . $base
            . " ORDER BY $sort $direction,id $direction LIMIT :limit OFFSET :offset",
        );
        $bindings = $parameters;
        $bindings['status_now'] = $now;
        foreach ($bindings as $key => $value) {
            $statement->bindValue(':' . $key, $value);
        }
        $statement->bindValue(':limit', $criteria->pager->pageSize, PDO::PARAM_INT);
        $statement->bindValue(
            ':offset',
            ($criteria->pager->page - 1) * $criteria->pager->pageSize,
            PDO::PARAM_INT,
        );
        $statement->execute();
        $rows = $statement->fetchAll(PDO::FETCH_ASSOC) ?: [];
        foreach ($rows as &$row) {
            $decoded = json_decode((string) $row['scopes'], true);
            $row['scopes'] = is_array($decoded)
                ? implode(', ', array_filter($decoded, 'is_string'))
                : '';
        }

        return new PaginationResult(
            $rows,
            (int) $count->fetchColumn(),
            $criteria->pager->page,
            $criteria->pager->pageSize,
        );
    }

    /** @param array<string, mixed> $row */
    private function renderName(mixed $value, array $row): string
    {
        $name = trim((string) $value);
        return '<span class="pat-token-name" title="' . $this->escape($name) . '">'
            . $this->escape($name) . '</span>';
    }

    /** @param array<string, mixed> $row */
    private function renderScopes(mixed $value, array $row): string
    {
        $scopes = array_values(array_filter(array_map('trim', explode(',', (string) $value))));
        if ($scopes === []) {
            return '<span class="pat-token-empty">None</span>';
        }

        $html = '<span class="pat-token-scopes" aria-label="Token scopes">';
        foreach ($scopes as $scope) {
            $html .= '<span class="pat-token-scope">' . $this->escape($scope) . '</span>';
        }
        return $html . '</span>';
    }

    /** @param array<string, mixed> $row */
    private function renderDate(mixed $value, array $row): string
    {
        $date = trim((string) $value);
        if ($date === '') {
            return '<span class="pat-token-empty">Never</span>';
        }

        return '<time class="pat-token-date" datetime="' . $this->escape(str_replace(' ', 'T', $date)) . '">'
            . $this->escape($date) . '</time>';
    }

    /** @param array<string, mixed> $row */
    private function renderStatus(mixed $value, array $row): string
    {
        $status = (string) $value;
        $modifier = match ($status) {
            'Active' => 'active',
            'Expired' => 'expired',
            default => 'revoked',
        };

        return '<span class="pat-token-status pat-token-status--' . $modifier . '">'
            . '<span aria-hidden="true"></span>' . $this->escape($status) . '</span>';
    }

    /** @param array<string, mixed> $row */
    private function renderActions(mixed $value, array $row): string
    {
        if (($row['revoked_at'] ?? null) !== null) {
            return '<span class="pat-token-empty">—</span>';
        }

        $action = $this->urls->url('access-tokens/' . (int) $row['id'] . '/revoke');
        return '<form method="post" action="' . $this->escape($action) . '">'
            . '<input type="hidden" name="_csrf_token" value="' . $this->escape($this->csrf) . '">'
            . '<button type="submit" class="button button--danger pat-token-revoke">Revoke</button>'
            . '</form>';
    }

    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
