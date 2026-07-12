<?php

declare(strict_types=1);

use Zoosper\Page\Admin\Form\PageContentSectionProvider;
use Zoosper\Page\Admin\Form\PageDetailsSectionProvider;
use Zoosper\Page\Admin\Form\PagePublishingSectionProvider;
use Zoosper\Page\Admin\Form\PageSeoSectionProvider;

return [
    /**
     * Admin form section providers grouped by stable form handle.
     *
     * Third-party modules can later contribute additional provider classes for
     * the same handle, for example `page.analytics` or `page.open_graph`,
     * without editing PageAdminController or overriding the full page form.
     */
    'forms' => [
        'page.form' => [
            PageDetailsSectionProvider::class,
            PageContentSectionProvider::class,
            PageSeoSectionProvider::class,
            PagePublishingSectionProvider::class,
        ],
    ],
];
