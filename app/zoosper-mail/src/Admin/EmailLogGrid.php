<?php

declare(strict_types=1);

namespace Zoosper\Mail\Admin;

use PDO;
use Zoosper\Core\Url\AdminUrlGenerator;
use Zoosper\Grid\GridColumn;
use Zoosper\Grid\GridCriteria;
use Zoosper\Grid\GridDataSourceInterface;
use Zoosper\Grid\GridDefinition;
use Zoosper\Grid\GridFilter;
use Zoosper\Pagination\PaginationResult;

/** Mail-owned, read-only Admin Grid for outbound SMTP diagnostics. */
final readonly class EmailLogGrid implements GridDataSourceInterface
{
    public const KEY = 'admin.email-logs';

    public function __construct(private PDO $pdo, private ?AdminUrlGenerator $urls = null)
    {
    }

    public function definition(): GridDefinition
    {
        return new GridDefinition(
            'Email Logs',
            [
                new GridColumn('id', 'ID', true, toggleable: false),
                new GridColumn('status', 'Status', true, render: $this->status(...)),
                new GridColumn('to_emails', 'To', true),
                new GridColumn('subject', 'Subject', true),
                new GridColumn('created_at', 'Created', true),
                new GridColumn('actions', 'Actions', toggleable: false, render: $this->actions(...)),
            ],
            [
                new GridFilter('status', 'Status', 'select', [
                    ['value' => 'sent', 'label' => 'Sent'],
                    ['value' => 'failed', 'label' => 'Failed'],
                ]),
                new GridFilter('email', 'Email'),
                new GridFilter('subject', 'Subject'),
            ],
            'created_at',
            'desc',
            'No email logs found.',
        );
    }

    public function paginate(GridCriteria $criteria): PaginationResult
    {
        [$where, $parameters] = $this->where($criteria);
        $count = $this->pdo->prepare('SELECT COUNT(*) FROM smtp_email_log ' . $where);
        $this->bind($count, $parameters);
        $count->execute();
        $sort = [
            'id' => 'id',
            'status' => 'status',
            'to_emails' => 'to_emails',
            'subject' => 'subject',
            'created_at' => 'created_at',
        ][$criteria->sortBy ?? 'created_at'] ?? 'created_at';
        $direction = $criteria->sortDir === 'asc' ? 'ASC' : 'DESC';
        $statement = $this->pdo->prepare(
            'SELECT id,status,to_emails,subject,created_at FROM smtp_email_log '
            . $where . " ORDER BY {$sort} {$direction}, id {$direction} LIMIT :limit OFFSET :offset",
        );
        $this->bind($statement, $parameters);
        $statement->bindValue(':limit', $criteria->pager->pageSize, PDO::PARAM_INT);
        $statement->bindValue(':offset', $criteria->pager->offset(), PDO::PARAM_INT);
        $statement->execute();
        return new PaginationResult(
            items: $statement->fetchAll(PDO::FETCH_ASSOC) ?: [],
            total: (int) $count->fetchColumn(),
            page: $criteria->pager->page,
            pageSize: $criteria->pager->pageSize,
        );
    }

    /** @return array{0:string,1:array<string,string>} */
    private function where(GridCriteria $criteria): array
    {
        $conditions = [];
        $parameters = [];
        $status = trim((string) ($criteria->filters['status'] ?? ''));
        if (in_array($status, ['sent', 'failed'], true)) {
            $conditions[] = 'status = :status';
            $parameters['status'] = $status;
        }
        $email = trim((string) ($criteria->filters['email'] ?? ''));
        if ($email !== '') {
            $conditions[] = '(from_email LIKE :email_from OR to_emails LIKE :email_to)';
            $parameters['email_from'] = '%' . $email . '%';
            $parameters['email_to'] = '%' . $email . '%';
        }
        $subject = trim((string) ($criteria->filters['subject'] ?? ''));
        if ($subject !== '') {
            $conditions[] = 'subject LIKE :subject';
            $parameters['subject'] = '%' . $subject . '%';
        }
        return [$conditions === [] ? '' : 'WHERE ' . implode(' AND ', $conditions), $parameters];
    }

    /** @param array<string,string> $parameters */
    private function bind(\PDOStatement $statement, array $parameters): void
    {
        foreach ($parameters as $name => $value) {
            $statement->bindValue(':' . $name, $value, PDO::PARAM_STR);
        }
    }

    /** @param array<string,mixed> $row */
    private function status(mixed $value, array $row): string
    {
        $status = in_array((string) $value, ['sent', 'failed'], true) ? (string) $value : 'unknown';
        return '<span class="badge badge-' . $this->escape($status) . '">' . $this->escape($status) . '</span>';
    }

    /** @param array<string,mixed> $row */
    private function actions(mixed $value, array $row): string
    {
        $url = ($this->urls?->url('mail-logs/view') ?? '/admin/mail-logs/view') . '?id=' . (int) $row['id'];
        return '<a href="' . $this->escape($url) . '">View</a>';
    }

    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
