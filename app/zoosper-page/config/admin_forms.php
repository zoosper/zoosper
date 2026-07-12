<?php

declare(strict_types=1);

use Zoosper\Page\Admin\Form\PageContentSectionProvider;
use Zoosper\Page\Admin\Form\PageDetailsSectionProvider;
use Zoosper\Page\Admin\Form\PagePublishingSectionProvider;
use Zoosper\Page\Admin\Form\PageSeoSectionProvider;

return [
    /**
     * Page admin form section providers.
     *
     * These providers render the core page editing sections while allowing
     * third-party modules to add more sections for the same `page.form` handle.
     */
    'forms' => [
        'page.form' => [
            PageDetailsSectionProvider::class,
            PageContentSectionProvider::class,
            PageSeoSectionProvider::class,
            PagePublishingSectionProvider::class,
        ],
    ],

    /**
     * Page admin form processors.
     *
     * The key is intentionally present even when no core processors are
     * registered yet. Third-party modules can contribute processors for this
     * handle and the verifier can confirm the extension point is available.
     */
    'processors' => [
        'page.form' => [],
    ],
];
