<?php

declare(strict_types=1);
namespace Zoosper\Page\Repository;
use PDO;
use RuntimeException;
use Zoosper\Page\Model\PageRevision;
final readonly class PageRevisionRepository
{
    public function __construct(private PDO $pdo) {}
    /** @return list<PageRevision> */
    public function forPage(int $pageId, int $limit = 50): array
    {
        $limit = max(1, min(200, $limit));
        $statement = $this->pdo->prepare("SELECT * FROM page_revisions WHERE page_id = :page_id ORDER BY id DESC LIMIT {$limit}");
        $statement->execute(['page_id' => $pageId]);
        return array_map($this->hydrate(...), $statement->fetchAll(PDO::FETCH_ASSOC));
    }
    /** @return list<PageRevision> */
    public function pageForPage(int $pageId, int $page, int $pageSize): array
    {
        $page = max(1, $page);
        $pageSize = max(1, min(50, $pageSize));
        $offset = ($page - 1) * $pageSize;
        $statement = $this->pdo->prepare(
            "SELECT * FROM page_revisions WHERE page_id = :page_id ORDER BY id DESC LIMIT {$pageSize} OFFSET {$offset}"
        );
        $statement->execute(['page_id' => $pageId]);

        return array_map($this->hydrate(...), $statement->fetchAll(PDO::FETCH_ASSOC));
    }

    public function countForPage(int $pageId): int
    {
        $statement = $this->pdo->prepare('SELECT COUNT(*) FROM page_revisions WHERE page_id = :page_id');
        $statement->execute(['page_id' => $pageId]);

        return (int) $statement->fetchColumn();
    }

    public function findForPage(int $revisionId, int $pageId): ?PageRevision
    {
        $statement = $this->pdo->prepare('SELECT * FROM page_revisions WHERE id = :id AND page_id = :page_id LIMIT 1');
        $statement->execute(['id' => $revisionId, 'page_id' => $pageId]);
        $row = $statement->fetch(PDO::FETCH_ASSOC);
        return is_array($row) ? $this->hydrate($row) : null;
    }
    /** @param array<string,mixed> $snapshot */
    public function capture(int $pageId, array $snapshot, ?int $createdBy): int
    {
        $statement = $this->pdo->prepare('INSERT INTO page_revisions (page_id,title,slug,content,status,content_format,content_json,meta_title,meta_description,meta_keywords,canonical_url,created_by,created_at) VALUES (:page_id,:title,:slug,:content,:status,:content_format,:content_json,:meta_title,:meta_description,:meta_keywords,:canonical_url,:created_by,:created_at)');
        $statement->execute(['page_id'=>$pageId,'title'=>$snapshot['title'],'slug'=>$snapshot['slug'],'content'=>$snapshot['content'],'status'=>$snapshot['status'],'content_format'=>$snapshot['content_format'] ?? 'html','content_json'=>$snapshot['content_json'] ?? null,'meta_title'=>$snapshot['meta_title'] ?? null,'meta_description'=>$snapshot['meta_description'] ?? null,'meta_keywords'=>$snapshot['meta_keywords'] ?? null,'canonical_url'=>$snapshot['canonical_url'] ?? null,'created_by'=>$createdBy,'created_at'=>gmdate('Y-m-d H:i:s')]);
        return (int) $this->pdo->lastInsertId();
    }
    public function deleteForPage(int $pageId): int
    {
        $statement = $this->pdo->prepare('DELETE FROM page_revisions WHERE page_id = :page_id');
        $statement->execute(['page_id' => $pageId]);
        return $statement->rowCount();
    }
    public function prune(int $pageId, int $retain): int
    {
        $retain=max(1,$retain); $ids=$this->pdo->query('SELECT id FROM page_revisions WHERE page_id='.(int)$pageId.' ORDER BY id DESC')->fetchAll(PDO::FETCH_COLUMN);
        $delete=array_slice($ids,$retain); if($delete===[]){return 0;}
        $statement=$this->pdo->prepare('DELETE FROM page_revisions WHERE id IN ('.implode(',',array_fill(0,count($delete),'?')).')'); $statement->execute($delete); return $statement->rowCount();
    }
    /** @param array<string,mixed> $row */
    private function hydrate(array $row): PageRevision
    {
        return new PageRevision((int)$row['id'],(int)$row['page_id'],(string)$row['title'],(string)($row['slug']??''),(string)$row['content'],(string)($row['status']??'draft'),(string)($row['content_format']??'html'),isset($row['content_json'])?(string)$row['content_json']:null,isset($row['meta_title'])?(string)$row['meta_title']:null,isset($row['meta_description'])?(string)$row['meta_description']:null,isset($row['meta_keywords'])?(string)$row['meta_keywords']:null,isset($row['canonical_url'])?(string)$row['canonical_url']:null,isset($row['created_by'])?(int)$row['created_by']:null,(string)$row['created_at']);
    }
}
