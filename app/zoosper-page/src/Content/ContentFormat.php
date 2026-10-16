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

}










