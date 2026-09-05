<?php

declare(strict_types=1);

namespace Zoosper\Media\EditorJs;

use PDO;
use Zoosper\Media\Model\MediaAsset;
use Zoosper\Pagination\Pager;
use Zoosper\Pagination\PaginationResult;

/** Reads only active, published image assets that are safe for editor selection. */
final readonly class MediaPickerReadRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    /** @return PaginationResult<MediaAsset> */
    public function paginate(MediaPickerReadQuery $query): PaginationResult
    {
        $where = ["status = 'active'", "mime_type LIKE :mime_prefix", 'public_path IS NOT NULL'];
        $parameters = ['mime_prefix' => 'image/%'];

        if ($query->query !== null) {
            $where[] = '(filename LIKE :filename_query OR original_filename LIKE :original_filename_query)';
            $parameters['filename_query'] = '%' . $query->query . '%';
            $parameters['original_filename_query'] = '%' . $query->query . '%';
        }

        $from = ' FROM media_assets WHERE ' . implode(' AND ', $where);
        $count = $this->pdo->prepare('SELECT COUNT(*)' . $from);
        $count->execute($parameters);
        $total = (int) $count->fetchColumn();
        $pageCount = max(1, (int) ceil($total / $query->pager->pageSize));
        $pager = new Pager(min($query->pager->page, $pageCount), $query->pager->pageSize);

        $statement = $this->pdo->prepare(
            'SELECT *' . $from . ' ORDER BY created_at DESC, id DESC LIMIT :limit OFFSET :offset'
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
            publicPath: (string) $row['public_path'],
            status: (string) $row['status'],
            createdBy: $row['created_by'] !== null ? (int) $row['created_by'] : null,
            createdAt: $row['created_at'] !== null ? (string) $row['created_at'] : null,
            updatedAt: $row['updated_at'] !== null ? (string) $row['updated_at'] : null,
        );
    }
}
