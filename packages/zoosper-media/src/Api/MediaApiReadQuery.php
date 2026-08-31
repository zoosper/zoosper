<?php

declare(strict_types=1);

namespace Zoosper\Media\Api;

use Zoosper\Core\Http\Request;
use Zoosper\Media\Repository\MediaAssetCriteria;
use Zoosper\Pagination\Pager;

/** Converts untrusted Media API query values into an allow-listed read criterion. */
final class MediaApiReadQuery
{
    public static function fromRequest(Request $request): MediaAssetCriteria
    {
        $status = self::text($request->query('status'), 32);
        if (!in_array($status, ['active', 'archived'], true)) {
            $status = null;
        }

        $sort = self::text($request->query('sort'), 32) ?? 'created_at';
        if (!in_array($sort, MediaAssetCriteria::SORTABLE_FIELDS, true)) {
            $sort = 'created_at';
        }

        return new MediaAssetCriteria(
            pager: Pager::fromQuery([
                'page' => $request->query('page', '1'),
                'page_size' => $request->query('page_size', '20'),
            ]),
            query: self::text($request->query('q'), 200),
            status: $status,
            mimeType: self::text($request->query('mime_type'), 120),
            extension: self::extension($request->query('extension')),
            sortBy: $sort,
            sortDirection: $request->query('dir', 'desc') === 'asc' ? 'asc' : 'desc',
        );
    }

    private static function text(mixed $value, int $maximumLength): ?string
    {
        if (!is_string($value)) {
            return null;
        }

        $value = trim($value);
        if ($value === '') {
            return null;
        }

        return substr($value, 0, $maximumLength);
    }

    private static function extension(mixed $value): ?string
    {
        $extension = self::text($value, 16);

        return $extension === null ? null : strtolower(ltrim($extension, '.'));
    }
}











