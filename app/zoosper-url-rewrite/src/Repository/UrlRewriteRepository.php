<?php

declare(strict_types=1);

namespace Zoosper\UrlRewrite\Repository;

use PDO;
use Zoosper\UrlRewrite\Model\UrlRewrite;

/**
 * Repository for frontend URL rewrite records.
 *
 * This repository is intentionally scoped to simple request-path lookups for
 * Phase 0.24. Future phases can add admin CRUD, import/export and conflict
 * detection without changing frontend resolution code.
 */
final readonly class UrlRewriteRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    /**
     * Find an active rewrite for a site and request path.
     */
    public function findActiveByRequestPath(int $siteId, string $requestPath): ?UrlRewrite
    {
        $statement = $this->pdo->prepare(
            'SELECT * FROM url_rewrites WHERE site_id = :site_id AND request_path = :request_path AND is_active = 1 LIMIT 1'
        );
        $statement->execute([
            'site_id' => $siteId,
            'request_path' => trim($requestPath, '/'),
        ]);

        $row = $statement->fetch();
        return is_array($row) ? $this->hydrate($row) : null;
    }

    /** @return list<UrlRewrite> */
    public function allForSite(int $siteId): array
    {
        $s=$this->pdo->prepare('SELECT * FROM url_rewrites WHERE site_id=:site_id ORDER BY request_path ASC,id ASC');
        $s->execute(['site_id'=>$siteId]);
        return array_map(fn(array $row):UrlRewrite=>$this->hydrate($row),$s->fetchAll(PDO::FETCH_ASSOC));
    }

    public function save(?int $id,int $siteId,string $requestPath,string $targetPath,int $redirectType,bool $active=true):int
    {
        $now=date('Y-m-d H:i:s');$data=['site_id'=>$siteId,'request_path'=>trim($requestPath,'/'),'target_path'=>$targetPath,'redirect_type'=>$redirectType,'is_active'=>$active?1:0,'updated_at'=>$now];
        if($id===null){$s=$this->pdo->prepare("INSERT INTO url_rewrites(site_id,request_path,target_path,entity_type,entity_id,redirect_type,is_active,created_at,updated_at) VALUES(:site_id,:request_path,:target_path,'custom',NULL,:redirect_type,:is_active,:created_at,:updated_at)");$s->execute($data+['created_at'=>$now]);return (int)$this->pdo->lastInsertId();}
        $s=$this->pdo->prepare('UPDATE url_rewrites SET request_path=:request_path,target_path=:target_path,redirect_type=:redirect_type,is_active=:is_active,updated_at=:updated_at WHERE id=:id AND site_id=:site_id');$s->execute($data+['id'=>$id]);return $id;
    }

    /**
     * Hydrate a URL rewrite model from a database row.
     *
     * @param array<string, mixed> $row
     */
    private function hydrate(array $row): UrlRewrite
    {
        return new UrlRewrite(
            id: (int) $row['id'],
            siteId: (int) $row['site_id'],
            requestPath: (string) $row['request_path'],
            targetPath: (string) $row['target_path'],
            entityType: (string) $row['entity_type'],
            entityId: isset($row['entity_id']) ? (int) $row['entity_id'] : null,
            redirectType: (int) $row['redirect_type'],
            isActive: (bool) $row['is_active'],
        );
    }
}
