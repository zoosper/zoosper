<?php

declare(strict_types=1);

use Zoosper\Core\Http\Request;
use Zoosper\Media\Api\MediaApiReadQuery;

it('normalises and bounds untrusted Media collection query values', function (): void {
    $request = new Request('GET', '/api/v1/media', query: [
        'page' => '999999999',
        'page_size' => '999',
        'q' => ' product ',
        'status' => 'active',
        'mime_type' => 'image/webp',
        'extension' => '.WEBP',
        'sort' => 'size_bytes',
        'dir' => 'asc',
    ]);

    $criteria = MediaApiReadQuery::fromRequest($request);

    expect($criteria->pager->page)->toBe(100_000)
        ->and($criteria->pager->pageSize)->toBe(100)
        ->and($criteria->query)->toBe('product')
        ->and($criteria->status)->toBe('active')
        ->and($criteria->mimeType)->toBe('image/webp')
        ->and($criteria->extension)->toBe('webp')
        ->and($criteria->sortBy)->toBe('size_bytes')
        ->and($criteria->sortDirection)->toBe('asc');
});

it('falls back safely for unsupported Media collection controls', function (): void {
    $request = new Request('GET', '/api/v1/media', query: [
        'status' => 'deleted',
        'sort' => 'storage_path',
        'dir' => 'sideways',
    ]);

    $criteria = MediaApiReadQuery::fromRequest($request);

    expect($criteria->status)->toBeNull()
        ->and($criteria->sortBy)->toBe('created_at')
        ->and($criteria->sortDirection)->toBe('desc');
});











