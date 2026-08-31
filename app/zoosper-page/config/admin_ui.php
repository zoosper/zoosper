<?php

declare(strict_types=1);

return [
    'admin.pages.form' => [
        'sections' => [
            'details' => ['title' => 'Page details', 'description' => 'Choose the site and define the public page identity.'],
            'content' => ['title' => 'Content', 'description' => 'Manage page body content and visual layout.'],
            'seo' => ['title' => 'Search engine optimisation', 'description' => 'Optional search metadata kept separate from the page body.'],
            'publishing' => ['title' => 'Publishing', 'description' => 'Control publication state and save your changes.'],
        ],
        'fields' => [
            'site_id' => ['type' => 'select', 'label' => 'Site', 'section' => 'details', 'sort_order' => 10],
            'title' => ['type' => 'text', 'label' => 'Title', 'section' => 'details', 'required' => true, 'sort_order' => 20],
            'slug' => ['type' => 'text', 'label' => 'Slug', 'section' => 'details', 'required' => true, 'sort_order' => 30],

            'content_html' => ['type' => 'html', 'label' => 'Content', 'section' => 'content', 'sort_order' => 10],

            'meta_title' => ['type' => 'text', 'label' => 'Meta title', 'section' => 'seo', 'sort_order' => 10],
            'meta_description' => ['type' => 'textarea', 'label' => 'Meta description', 'section' => 'seo', 'rows' => 3, 'sort_order' => 20],
            'meta_keywords' => ['type' => 'text', 'label' => 'Meta keywords', 'section' => 'seo', 'sort_order' => 30],
            'canonical_url' => ['type' => 'url', 'label' => 'Canonical URL', 'section' => 'seo', 'sort_order' => 40],

            'status' => ['type' => 'select', 'label' => 'Status', 'section' => 'publishing', 'sort_order' => 10, 'options' => ['draft' => 'Draft', 'published' => 'Published', 'archived' => 'Archived']],
            'publish' => ['type' => 'checkbox', 'label' => 'Publish page', 'section' => 'publishing', 'sort_order' => 20],
        ],
    ],
];
