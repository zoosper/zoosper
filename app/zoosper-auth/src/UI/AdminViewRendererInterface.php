<?php

declare(strict_types=1);

namespace Zoosper\Auth\UI;

use Zoosper\Auth\Model\AdminUser;

/** Contract for rendering an admin view template inside the shared admin layout/shell. */
interface AdminViewRendererInterface
{
    /** @param array<string, mixed> $data */
    public function render(string $title, string $template, array $data, ?AdminUser $user, string $active = 'dashboard'): string;
}
