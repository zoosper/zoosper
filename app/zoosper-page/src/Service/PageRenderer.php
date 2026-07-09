<?php

declare(strict_types=1);

namespace Zoosper\Page\Service;

use Zoosper\Page\Model\Page;
use Zoosper\Site\Model\Site;

final readonly class PageRenderer
{
    public function render(Page $page, Site $site): string
    {
        $title = htmlspecialchars($page->title, ENT_QUOTES, 'UTF-8');
        $siteName = htmlspecialchars($site->name, ENT_QUOTES, 'UTF-8');
        $content = nl2br(htmlspecialchars($page->content, ENT_QUOTES, 'UTF-8'));
        $metaTitle = htmlspecialchars($page->metaTitle ?: $page->title, ENT_QUOTES, 'UTF-8');
        $metaDescription = htmlspecialchars($page->metaDescription ?: '', ENT_QUOTES, 'UTF-8');

        return <<<HTML
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{$metaTitle}</title>
    <meta name="description" content="{$metaDescription}">
    <style>
        body {
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            margin: 0;
            color: #102a43;
            background: #f8fafc;
        }
        header, main, footer {
            max-width: 920px;
            margin: 0 auto;
            padding: 1.5rem;
        }
        header {
            border-bottom: 1px solid #d9e2ec;
        }
        main article {
            background: white;
            border: 1px solid #d9e2ec;
            border-radius: 18px;
            padding: 2rem;
            box-shadow: 0 10px 24px rgba(15, 23, 42, 0.04);
        }
        .eyebrow {
            color: #0f766e;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            font-size: 0.75rem;
        }
    </style>
</head>
<body>
    <header>
        <strong>{$siteName}</strong>
    </header>
    <main>
        <article>
            <div class="eyebrow">Zoosper CMS</div>
            <h1>{$title}</h1>
            <div>{$content}</div>
        </article>
    </main>
    <footer>
        <small>Rendered by Zoosper Phase 0.3</small>
    </footer>
</body>
</html>
HTML;
    }
}
