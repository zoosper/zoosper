<?php

declare(strict_types=1);

namespace Zoosper\Media\EditorJs;

use Zoosper\Core\Http\Response;
use Zoosper\Media\Model\MediaAsset;
use Zoosper\Media\Service\MediaDerivativeLookup;
use Zoosper\Pagination\PaginationResult;

/** Converts Media picker reads into a browser-safe JSON representation. */
final readonly class MediaPickerResponder
{
    public function __construct(private MediaDerivativeLookup $derivatives)
    {
    }

    /** @param PaginationResult<MediaAsset> $result */
    public function respond(PaginationResult $result): Response
    {
        return Response::json([
            'items' => array_map($this->item(...), $result->items),
            'pagination' => [
                'page' => $result->page,
                'page_size' => $result->pageSize,
                'page_count' => $result->totalPages(),
                'total' => $result->total,
                'has_previous' => $result->hasPrevious(),
                'has_next' => $result->hasNext(),
            ],
        ]);
    }

    /** @return array<string, int|string> */
    private function item(MediaAsset $asset): array
    {
        $url = (string) $asset->publicPath;

        return [
            'id' => $asset->id,
            'filename' => $asset->filename,
            'original_filename' => $asset->originalFilename,
            'mime_type' => $asset->mimeType,
            'size_bytes' => $asset->sizeBytes,
            'url' => $url,
            'thumbnail_url' => $this->derivatives->publicPath($asset, 'thumb') ?? $url,
        ];
    }
}
