<?php

declare(strict_types=1);

namespace Zoosper\Page\Content;

/**
 * Supported page content storage formats.
 */
enum ContentFormat: string
{
    case Html = 'html';
    case BlockJson = 'block_json';
    case Markdown = 'markdown';

    public static function fromNullable(?string $format): self
    {
        return match ($format) {
            self::BlockJson->value => self::BlockJson,
            self::Markdown->value => self::Markdown,
            default => self::Html,
        };
    }
}
