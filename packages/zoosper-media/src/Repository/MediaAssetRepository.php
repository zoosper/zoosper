<?php

declare(strict_types=1);

namespace Zoosper\Media\Repository;

use PDO;
use Zoosper\Media\Model\MediaAsset;

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

    public function findById(int $id): ?MediaAsset
    {
        $statement = $this->pdo->prepare('SELECT * FROM media_assets WHERE id = :id LIMIT 1');
        $statement->execute(['id' => $id]);
        $row = $statement->fetch(PDO::FETCH_ASSOC);

        return is_array($row) ? $this->hydrate($row) : null;
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
