<?php

declare(strict_types=1);

namespace Zoosper\Core\Url;

/**
 * Known CDN URL channels.
 *
 * Keeping URL channels explicit avoids mixing dynamic store links with media or
 * static assets. This makes the behaviour easier to reason about when CDN,
 * cache, SEO, multisite and localisation support are expanded later.
 */
final class CdnUrlType
{
    public const DYNAMIC = 'dynamic';
    public const MEDIA = 'media';
    public const STATIC = 'static';

    /**
     * @return list<string>
     */
    public static function all(): array
    {
        return [self::DYNAMIC, self::MEDIA, self::STATIC];
    }
}
