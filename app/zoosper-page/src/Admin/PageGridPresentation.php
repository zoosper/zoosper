<?php

declare(strict_types=1);

namespace Zoosper\Page\Admin;

use Zoosper\AdminGrid\GridViewState;
use Zoosper\AdminGrid\GridWorkspaceMutationFormsRenderer;
use Zoosper\Core\Url\AdminUrlGenerator;

/** Combines the Pages workspace GET controls with protected POST controls. */
final readonly class PageGridPresentation
{
    public function __construct(
        private GridWorkspaceMutationFormsRenderer $mutations,
        private ?AdminUrlGenerator $adminUrls = null,
    ) {
    }

    public function render(
        string $workspaceHtml,
        GridViewState $state,
        string $csrfField,
        string $csrfToken,
    ): string {
        return $workspaceHtml . $this->mutations->render(
            $state,
            $this->adminUrls?->url('pages/grid') ?? '/admin/pages/grid',
            $csrfField,
            $csrfToken,
        );
    }
}
