<?php

declare(strict_types=1);

use Zoosper\Page\Admin\Form\PageContentSectionProvider;
use Zoosper\Page\Admin\Form\PageDetailsSectionProvider;
use Zoosper\Page\Admin\Form\PagePublishingSectionProvider;
use Zoosper\Page\Admin\Form\PageSeoSectionProvider;

return [
    'forms' => [
        'page.form' => [
            PageDetailsSectionProvider::class,
            PageContentSectionProvider::class,
            PageSeoSectionProvider::class,
            PagePublishingSectionProvider::class,
        ],
    ],

    /**
     * Page form processors will be added in a later phase when module-owned
     * fields need validation/persistence hooks during create/update flows.
     */
    'processors' => [
        'page.form' => [],
    ],
];
