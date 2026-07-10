<?php

declare(strict_types=1);

namespace Zoosper\Page\Repository;

use PDO;
use Zoosper\Page\Model\PageSiteAssignment;

/**
 * Persists multi-site/store-view assignments for CMS pages.
 *
 * The repository stores only page/site relationship data. It must not be used
 * for payment, authentication, OTP, recovery-code or other PCI-sensitive data.
 */
final readonly class PageSiteAssignmentRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    /**
     * Replace all assigned site IDs for a page.
     *
     * @param list<int> $siteIds Site/store-view IDs to assign.
     */
    public function replaceForPage(int $pageId, array $siteIds): void
    {
        $siteIds = array_values(array_unique(array_filter(
            array_map(static fn (int|string $siteId): int => (int) $siteId, $siteIds),
            static fn (int $siteId): bool => $siteId > 0,
        )));

        $this->pdo->beginTransaction();
        try {
            $delete = $this->pdo->prepare('DELETE FROM page_site_assignments WHERE page_id = :page_id');
            $delete->execute(['page_id' => $pageId]);

            $insert = $this->pdo->prepare(
                'INSERT INTO page_site_assignments (page_id, site_id, created_at) VALUES (:page_id, :site_id, CURRENT_TIMESTAMP)'
            );

            foreach ($siteIds as $siteId) {
                $insert->execute([
                    'page_id' => $pageId,
                    'site_id' => $siteId,
                ]);
            }

            $this->pdo->commit();
        } catch (\Throwable $exception) {
            $this->pdo->rollBack();
            throw $exception;
        }
    }

    /**
     * Return assigned site IDs for a page.
     *
     * @return list<int>
     */
    public function siteIdsForPage(int $pageId): array
    {
        $statement = $this->pdo->prepare('SELECT site_id FROM page_site_assignments WHERE page_id = :page_id ORDER BY site_id ASC');
        $statement->execute(['page_id' => $pageId]);

        return array_map(static fn (mixed $siteId): int => (int) $siteId, $statement->fetchAll(PDO::FETCH_COLUMN));
    }

    /**
     * Return assignment models for a page.
     *
     * @return list<PageSiteAssignment>
     */
    public function assignmentsForPage(int $pageId): array
    {
        $statement = $this->pdo->prepare('SELECT * FROM page_site_assignments WHERE page_id = :page_id ORDER BY site_id ASC');
        $statement->execute(['page_id' => $pageId]);

        return array_map(
            static fn (array $row): PageSiteAssignment => new PageSiteAssignment(
                id: (int) $row['id'],
                pageId: (int) $row['page_id'],
                siteId: (int) $row['site_id'],
            ),
            $statement->fetchAll(PDO::FETCH_ASSOC),
        );
    }
}
