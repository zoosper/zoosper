<?php

declare(strict_types=1);

return [
    'admin.pages.form' => [
        'fields' => [
            'site_id' => ['type' => 'select', 'label' => 'Site', 'sort_order' => 10],
            'title' => ['type' => 'text', 'label' => 'Title', 'required' => true, 'sort_order' => 20],
            'slug' => ['type' => 'text', 'label' => 'Slug', 'required' => true, 'sort_order' => 30],
            'status' => ['type' => 'select', 'label' => 'Status', 'sort_order' => 40, 'options' => ['draft' => 'Draft', 'published' => 'Published', 'archived' => 'Archived']],
            'content' => ['type' => 'textarea', 'label' => 'Content', 'rows' => 12, 'sort_order' => 50],
            'meta_title' => ['type' => 'text', 'label' => 'Meta title', 'sort_order' => 60],
        ],
    ],
];
