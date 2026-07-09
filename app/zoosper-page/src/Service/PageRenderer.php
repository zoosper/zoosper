<?php

declare(strict_types=1);

namespace Zoosper\Page\Service;

use Zoosper\Core\App\CmsVersion;
use Zoosper\Page\Model\Page;
use Zoosper\Site\Model\Site;

final readonly class PageRenderer
{
    public function __construct(private ?CmsVersion $version = null)
    {
    }

    public function render(Page $page, Site $site): string
    {
        $title = htmlspecialchars($page->metaTitle ?? $page->title, ENT_QUOTES, 'UTF-8');
        $heading = htmlspecialchars($page->title, ENT_QUOTES, 'UTF-8');
        $content = nl2br(htmlspecialchars($page->content, ENT_QUOTES, 'UTF-8'));
        $siteName = htmlspecialchars($site->name, ENT_QUOTES, 'UTF-8');
        $version = htmlspecialchars(($this->version ?? new CmsVersion())->label(), ENT_QUOTES, 'UTF-8');

        return <<<HTML
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{$title}</title>
    <style>
        body { font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; margin: 0; color: #102a43; background: #f8fafc; }
        header, main, footer { max-width: 960px; margin: 0 auto; padding: 1.5rem; }
        header { border-bottom: 1px solid #d9e2ec; background: #fff; }
        main { background: #fff; margin-top: 1.5rem; border: 1px solid #d9e2ec; border-radius: 1rem; }
        footer { color: #64748b; font-size: .9rem; }
    </style>
</head>
<body>
    <header>
        <strong>{$siteName}</strong>
    </header>
    <main>
        <h1>{$heading}</h1>
        <div>{$content}</div>
    </main>
    <footer>{$version}</footer>
</body>
</html>
HTML;
    }
}
