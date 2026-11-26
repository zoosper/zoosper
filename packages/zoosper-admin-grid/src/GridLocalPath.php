<?php

declare(strict_types=1);

namespace Zoosper\AdminGrid;

final readonly class GridLocalPath
{
    public static function isValid(string $path): bool
    {
        return $path !== ''
            && $path[0] === '/'
            && !str_starts_with($path, '//')
            && !str_contains($path, '\\')
            && !str_contains($path, '://')
            && preg_match('/[\x00-\x1f\x7f]/', $path) !== 1;
    }
}