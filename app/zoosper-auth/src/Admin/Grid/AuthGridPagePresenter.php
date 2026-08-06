<?php

declare(strict_types=1);

namespace Zoosper\Auth\Admin\Grid;

use Zoosper\Core\Url\AdminUrlGenerator;

/** Presents the complete Grid fragment while keeping primary CRUD actions explicit. */
final readonly class AuthGridPagePresenter
{
    public function __construct(private ?AdminUrlGenerator $adminUrls = null)
    {
    }

    public function present(AuthGridPage $page, string $createUrl, string $createLabel): string
    {
        $isAdminUrl = $this->adminUrls?->isAdminPath($createUrl)
            ?? str_starts_with($createUrl, '/admin/');
        if (!$isAdminUrl) {
            throw new \InvalidArgumentException('Auth Grid create URL must remain admin-local.');
        }

        return '<div class="admin-page-actions"><a href="'
            . $this->escape($createUrl)
            . '">' . $this->escape($createLabel) . '</a></div>'
            . $page->workspaceHtml
            . $page->gridHtml;
    }

    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
