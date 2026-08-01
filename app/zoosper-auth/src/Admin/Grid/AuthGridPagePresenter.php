<?php

declare(strict_types=1);

namespace Zoosper\Auth\Admin\Grid;

/** Presents the complete Grid fragment while keeping primary CRUD actions explicit. */
final readonly class AuthGridPagePresenter
{
    public function present(AuthGridPage $page, string $createUrl, string $createLabel): string
    {
        if (!str_starts_with($createUrl, '/admin/')) {
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
