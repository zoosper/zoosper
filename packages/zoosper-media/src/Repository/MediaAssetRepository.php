<?php

declare(strict_types=1);

namespace Zoosper\Media\Repository;

use PDO;
use Zoosper\Media\Model\MediaAsset;
use Zoosper\Pagination\Pager;
use Zoosper\Pagination\PaginationResult;

/**
 * Repository for media asset metadata.
 */
final readonly class MediaAssetRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function create(
        string $uuid,
        string $filename,
        string $originalFilename,
        string $mimeType,
        string $extension,
        int $sizeBytes,
        string $storagePath,
        ?string $publicPath,
        ?int $createdBy = null,
    ): int {
        $now = gmdate('Y-m-d H:i:s');
        $statement = $this->pdo->prepare(
            'INSERT INTO media_assets (uuid, filename, original_filename, mime_type, extension, size_bytes, storage_path, public_path, status, created_by, created_at, updated_at)
             VALUES (:uuid, :filename, :original_filename, :mime_type, :extension, :size_bytes, :storage_path, :public_path, :status, :created_by, :created_at, :updated_at)'
        );
        $statement->execute([
            'uuid' => $uuid,
            'filename' => $filename,
            'original_filename' => $originalFilename,
            'mime_type' => $mimeType,
            'extension' => $extension,
            'size_bytes' => $sizeBytes,
            'storage_path' => $storagePath,
            'public_path' => $publicPath,
            'status' => 'active',
            'created_by' => $createdBy,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    /** @return list<MediaAsset> */
    public function latest(int $limit = 100): array
    {
        $statement = $this->pdo->query('SELECT * FROM media_assets ORDER BY id DESC LIMIT ' . max(1, min(500, $limit)));
        $items = [];
        foreach ($statement->fetchAll(PDO::FETCH_ASSOC) ?: [] as $row) {
            if (is_array($row)) {
                $items[] = $this->hydrate($row);
            }
        }

        return $items;
    }

    /** @return PaginationResult<MediaAsset> */
    public function paginate(MediaAssetCriteria $criteria): PaginationResult
    {
        $where = ['1 = 1'];
        $parameters = [];

        if ($criteria->query !== null) {
            $where[] = '(filename LIKE :filename_query OR original_filename LIKE :original_filename_query)';
            $parameters['filename_query'] = '%' . $criteria->query . '%';
            $parameters['original_filename_query'] = '%' . $criteria->query . '%';
        }
        if ($criteria->status !== null) {
            $where[] = 'status = :status';
            $parameters['status'] = $criteria->status;
        }
        if ($criteria->mimeType !== null) {
            $where[] = 'mime_type = :mime_type';
            $parameters['mime_type'] = $criteria->mimeType;
        }
        if ($criteria->extension !== null) {
            $where[] = 'extension = :extension';
            $parameters['extension'] = $criteria->extension;
        }

        $from = ' FROM media_assets WHERE ' . implode(' AND ', $where);
        $count = $this->pdo->prepare('SELECT COUNT(*)' . $from);
        $count->execute($parameters);
        $total = (int) $count->fetchColumn();
        $pageCount = max(1, (int) ceil($total / $criteria->pager->pageSize));
        $pager = new Pager(min($criteria->pager->page, $pageCount), $criteria->pager->pageSize);
        $direction = $criteria->sortDirection === 'asc' ? 'ASC' : 'DESC';
        $statement = $this->pdo->prepare(
            'SELECT *' . $from
            . ' ORDER BY ' . $criteria->sortBy . ' ' . $direction . ', id ' . $direction
            . ' LIMIT :limit OFFSET :offset'
        );
        foreach ($parameters as $name => $value) {
            $statement->bindValue(':' . $name, $value);
        }
        $statement->bindValue(':limit', $pager->pageSize, PDO::PARAM_INT);
        $statement->bindValue(':offset', $pager->offset(), PDO::PARAM_INT);
        $statement->execute();

        $items = [];
        foreach ($statement->fetchAll(PDO::FETCH_ASSOC) ?: [] as $row) {
            if (is_array($row)) {
                $items[] = $this->hydrate($row);
            }
        }

        return new PaginationResult($items, $total, $pager->page, $pager->pageSize);
    }

    public function findById(int $id): ?MediaAsset
    {
        $statement = $this->pdo->prepare('SELECT * FROM media_assets WHERE id = :id LIMIT 1');
        $statement->execute(['id' => $id]);
        $row = $statement->fetch(PDO::FETCH_ASSOC);

        return is_array($row) ? $this->hydrate($row) : null;
    }

    public function changeStatus(int $id, string $status): void
    {
        if (!in_array($status, ['active', 'archived'], true)) {
            throw new \InvalidArgumentException('Unsupported Media status.');
        }
        $statement = $this->pdo->prepare('UPDATE media_assets SET status = :status, updated_at = :updated_at WHERE id = :id');
        $statement->execute(['status' => $status, 'updated_at' => gmdate('Y-m-d H:i:s'), 'id' => $id]);
        if ($statement->rowCount() !== 1) {
            throw new \RuntimeException('Media status was not changed.');
        }
    }

    public function deletePermanently(int $id): void
    {
        $statement = $this->pdo->prepare('DELETE FROM media_assets WHERE id = :id');
        $statement->execute(['id' => $id]);
        if ($statement->rowCount() !== 1) {
            throw new \RuntimeException('Media asset was not deleted.');
        }
    }

    /** @param array<string, mixed> $row */
    private function hydrate(array $row): MediaAsset
    {
        return new MediaAsset(
            id: (int) $row['id'],
            uuid: (string) $row['uuid'],
            filename: (string) $row['filename'],
            originalFilename: (string) $row['original_filename'],
            mimeType: (string) $row['mime_type'],
            extension: (string) $row['extension'],
            sizeBytes: (int) $row['size_bytes'],
            storagePath: (string) $row['storage_path'],
            publicPath: $row['public_path'] !== null ? (string) $row['public_path'] : null,
            status: (string) $row['status'],
            createdBy: $row['created_by'] !== null ? (int) $row['created_by'] : null,
            createdAt: $row['created_at'] !== null ? (string) $row['created_at'] : null,
            updatedAt: $row['updated_at'] !== null ? (string) $row['updated_at'] : null,
        );
    }
}











