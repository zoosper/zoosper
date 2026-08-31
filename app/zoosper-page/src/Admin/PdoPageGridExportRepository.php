<?php

declare(strict_types=1);

namespace Zoosper\Page\Admin;

use PDO;
use PDOStatement;
use RuntimeException;

/**
 * Streams Page Grid export rows from PDO using separately bound SQL parameters.
 *
 * The SELECT intentionally exposes stable Grid keys and joins Site for its
 * administrator-facing name. Export pagination is controlled by the outer
 * GridWorkspaceExportPolicy rather than screen LIMIT/OFFSET state.
 */
final readonly class PdoPageGridExportRepository implements PageGridExportRepositoryInterface
{
    private const SELECT = <<<'SQL'
SELECT
    p.id,
    p.title,
    p.slug,
    p.status,
    p.site_id,
    s.name AS site_name,
    p.created_at,
    p.updated_at
FROM pages p
LEFT JOIN sites s ON s.id = p.site_id
SQL;

    public function __construct(
        private PDO $pdo,
        private PageGridExportSqlBuilder $sql,
    ) {
    }

    public function stream(PageGridExportCriteria $criteria): iterable
    {
        $plan = $this->sql->build($criteria);
        $statement = $this->pdo->prepare(
            self::SELECT . "\n" . $plan->whereSql . "\n" . $plan->orderSql,
        );
        if (!$statement instanceof PDOStatement) {
            throw new RuntimeException('Unable to prepare the Page Grid export query.');
        }

        foreach ($plan->parameters as $name => $value) {
            $statement->bindValue(
                ':' . $name,
                $value,
                is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR,
            );
        }
        $statement->execute();

        while (($row = $statement->fetch(PDO::FETCH_ASSOC)) !== false) {
            yield $row;
        }
    }
}










