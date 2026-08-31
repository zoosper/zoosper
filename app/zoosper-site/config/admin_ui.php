<?php

declare(strict_types=1);

return [
    'admin.sites.form' => [
        'sections' => [
            'general' => ['title' => 'Site configuration', 'description' => 'Core identity and routing for this site.'],
            'localisation' => ['title' => 'Localisation', 'description' => 'Regional and currency settings.'],
            'system' => ['title' => 'System codes', 'description' => 'Advanced identifiers for technical integration.'],
        ],
        'fields' => [
            'name' => ['type' => 'text', 'label' => 'Name', 'required' => true, 'sort_order' => 10, 'section' => 'general'],
            'code' => ['type' => 'text', 'label' => 'Code', 'required' => true, 'sort_order' => 20, 'section' => 'general'],
            'host' => ['type' => 'text', 'label' => 'Primary host', 'required' => true, 'sort_order' => 30, 'section' => 'general'],
            'status' => ['type' => 'select', 'label' => 'Status', 'sort_order' => 40, 'section' => 'general', 'options' => ['active' => 'Active', 'inactive' => 'Inactive']],
            
            'locale' => ['type' => 'text', 'label' => 'Locale', 'sort_order' => 50, 'section' => 'localisation'],
            'currency' => ['type' => 'text', 'label' => 'Currency', 'sort_order' => 60, 'section' => 'localisation'],
            
            'homepage_slug' => ['type' => 'text', 'label' => 'Homepage slug', 'sort_order' => 70, 'section' => 'general'],
            'theme_code' => ['type' => 'text', 'label' => 'Theme code', 'sort_order' => 80, 'section' => 'general'],
            'path_prefix' => ['type' => 'text', 'label' => 'Path prefix', 'sort_order' => 90, 'section' => 'general'],
            
            'website_code' => ['type' => 'text', 'label' => 'Website code', 'sort_order' => 100, 'section' => 'system'],
            'store_code' => ['type' => 'text', 'label' => 'Store code', 'sort_order' => 110, 'section' => 'system'],
            'store_view_code' => ['type' => 'text', 'label' => 'Store view code', 'sort_order' => 120, 'section' => 'system'],
        ],
    ],
];










