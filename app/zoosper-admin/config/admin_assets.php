<?php

declare(strict_types=1);

$assetVersion = '1.37l';

return [
    'assets' => [
        'zoosper-grid-compact-script' => [
            'type' => 'script',
            'path' => '/asset/zoosper-admin/js/zoosper-grid-compact.js?v=' . $assetVersion,
            'sort_order' => 80,
            'attributes' => ['defer' => true],
        ],
        'zoosper-grid-columns-script' => [
            'type' => 'script',
            'path' => '/asset/zoosper-admin/js/zoosper-grid-columns.js?v=' . $assetVersion,
            'sort_order' => 81,
            'attributes' => ['defer' => true],
        ],
        'zoosper-admin-base' => [
            'type' => 'style',
            'path' => '/assets/admin/css/admin.css?v=' . $assetVersion,
            'sort_order' => 10,
        ],
        'zoosper-admin-messages-style' => [
            'type' => 'style',
            'path' => '/assets/admin/css/zoosper-admin-messages.css?v=' . $assetVersion,
            'sort_order' => 20,
        ],
        'zoosper-admin-editor-style' => [
            'type' => 'style',
            'path' => '/assets/admin/css/zoosper-content-editor.css?v=' . $assetVersion,
            'sort_order' => 30,
        ],
        // Phase C1/C2: served through the module asset pipeline
        // (GET /asset/zoosper-admin/css/zoosper-grid.css), resolved directly
        // from app/zoosper-admin/resources/assets/css/zoosper-grid.css — no
        // publish step, no manual copy into public/.
        'zoosper-admin-grid-style' => [
            'type' => 'style',
            'path' => '/asset/zoosper-admin/css/zoosper-grid.css?v=' . $assetVersion,
            'sort_order' => 35,
        ],
        // Phase D1-hotfix: page-momentum.css already exists on disk at
        // app/zoosper-admin/resources/assets/css/page-momentum.css (confirmed
        // present when the zoosper-grid.css publish-path investigation ran
        // `find app/zoosper-admin/resources/assets -maxdepth 3`), but had NO
        // admin_assets.php entry under EITHER the old /assets/admin/ mechanism
        // or the new pipeline — flagged as a known gap in Phase C1's README
        // and completed here as the promised one-line follow-up. Served via
        // the same no-copy module asset pipeline as zoosper-grid.css.
        'zoosper-admin-page-momentum-style' => [
            'type' => 'style',
            'path' => '/asset/zoosper-admin/css/page-momentum.css?v=' . $assetVersion,
            'sort_order' => 36,
        ],
        'zoosper-admin-tag-selector-style' => [
            'type' => 'style',
            'path' => '/assets/admin/css/zoosper-tag-selector.css',
            'sort_order' => 40,
        ],
        'zoosper-admin-messages-script' => [
            'type' => 'script',
            'path' => '/assets/admin/js/zoosper-admin-messages.js?v=' . $assetVersion,
            'sort_order' => 20,
            'attributes' => ['defer' => true],
        ],
        'zoosper-admin-editorjs-bundle' => [
            'type' => 'script',
            'path' => '/assets/admin/js/editorjs.bundle.js?v=' . $assetVersion,
            'sort_order' => 25,
            'attributes' => ['defer' => true],
        ],
        'zoosper-admin-editor-script' => [
            'type' => 'script',
            'path' => '/assets/admin/js/zoosper-content-editor.js?v=' . $assetVersion,
            'sort_order' => 30,
            'attributes' => ['defer' => true],
        ],
    ],
];
