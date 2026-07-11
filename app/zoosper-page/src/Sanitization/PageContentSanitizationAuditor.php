<?php

declare(strict_types=1);

namespace Zoosper\Page\Sanitization;

use PDO;
use Zoosper\Core\Html\HtmlSanitizerInterface;

/**
 * Audits and repairs existing CMS page HTML that predates save-time sanitisation.
 *
 * This service compares stored HTML against the configured HtmlSanitizerInterface
 * and records metadata about changed rows. It must not log or expose full HTML
 * content by default because CMS content may contain sensitive business data.
 */
final readonly class PageContentSanitizationAuditor
{
    public function __construct(private PDO $pdo, private HtmlSanitizerInterface $sanitizer)
    {
    }

    /**
     * Audit current `pages.content` rows without modifying data.
     */
    public function auditPages(int $sampleLimit = 10): PageContentSanitizationResult
    {
        return $this->scan('pages', 'id', 'content', repair: false, sampleLimit: $sampleLimit);
    }

    /**
     * Audit historical `page_revisions.content` rows without modifying data.
     */
    public function auditRevisions(int $sampleLimit = 10): PageContentSanitizationResult
    {
        return $this->scan('page_revisions', 'id', 'content', repair: false, sampleLimit: $sampleLimit);
    }

    /**
     * Repair current `pages.content` rows by replacing changed content with sanitised HTML.
     */
    public function repairPages(int $sampleLimit = 10): PageContentSanitizationResult
    {
        return $this->scan('pages', 'id', 'content', repair: true, sampleLimit: $sampleLimit);
    }

    /**
     * Repair historical `page_revisions.content` rows by replacing changed content with sanitised HTML.
     */
    public function repairRevisions(int $sampleLimit = 10): PageContentSanitizationResult
    {
        return $this->scan('page_revisions', 'id', 'content', repair: true, sampleLimit: $sampleLimit);
    }

    private function scan(string $table, string $idColumn, string $contentColumn, bool $repair, int $sampleLimit): PageContentSanitizationResult
    {
        if (!$this->tableExists($table)) {
            return new PageContentSanitizationResult($table, false);
        }

        $result = new PageContentSanitizationResult($table, true);
        $select = $this->pdo->query('SELECT `' . $idColumn . '`, `' . $contentColumn . '` FROM `' . $table . '` ORDER BY `' . $idColumn . '` ASC');
        $rows = $select !== false ? $select->fetchAll(PDO::FETCH_ASSOC) : [];

        $update = null;
        if ($repair) {
            $this->pdo->beginTransaction();
            $update = $this->pdo->prepare('UPDATE `' . $table . '` SET `' . $contentColumn . '` = :content WHERE `' . $idColumn . '` = :id');
        }

        try {
            foreach ($rows as $row) {
                $result->scanned++;
                $id = (int) ($row[$idColumn] ?? 0);
                $before = (string) ($row[$contentColumn] ?? '');
                $after = $this->sanitizer->sanitise($before)->toString();

                if ($before === $after) {
                    continue;
                }

                if (count($result->changedRows()) < $sampleLimit) {
                    $result->addChangedRow(
                        id: $id,
                        beforeLength: strlen($before),
                        afterLength: strlen($after),
                        beforeHash: hash('sha256', $before),
                        afterHash: hash('sha256', $after),
                        patterns: $this->detectPatterns($before, $after),
                    );
                } else {
                    $result->changed++;
                }

                if ($repair && $update !== null) {
                    $update->execute(['id' => $id, 'content' => $after]);
                    $result->markRepaired();
                }
            }

            if ($repair) {
                $this->pdo->commit();
            }
        } catch (\Throwable $exception) {
            if ($repair && $this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $exception;
        }

        return $result;
    }

    private function tableExists(string $table): bool
    {
        $driver = (string) $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME);

        if ($driver === 'sqlite') {
            $statement = $this->pdo->prepare("SELECT name FROM sqlite_master WHERE type = 'table' AND name = :table LIMIT 1");
            $statement->execute(['table' => $table]);

            return $statement->fetchColumn() !== false;
        }

        $statement = $this->pdo->prepare('SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table LIMIT 1');
        $statement->execute(['table' => $table]);

        return $statement->fetchColumn() !== false;
    }

    /**
     * @return list<string>
     */
    private function detectPatterns(string $before, string $after): array
    {
        $patterns = [];
        $beforeLower = strtolower($before);
        $afterLower = strtolower($after);

        if (str_contains($beforeLower, '<script') && !str_contains($afterLower, '<script')) {
            $patterns[] = 'script_tag_removed';
        }

        if (preg_match('/\son[a-z]+\s*=/i', $before) === 1 && preg_match('/\son[a-z]+\s*=/i', $after) !== 1) {
            $patterns[] = 'event_handler_removed';
        }

        if (str_contains($beforeLower, 'javascript:') && !str_contains($afterLower, 'javascript:')) {
            $patterns[] = 'javascript_url_removed';
        }

        if ($patterns === []) {
            $patterns[] = 'html_normalised';
        }

        return $patterns;
    }
}
