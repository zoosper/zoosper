<?php

declare(strict_types=1);

return [
    'admin.media.upload.form' => [
        'sections' => [
            'default' => [
                'title' => 'Upload media',
                'description' => 'Allowed image types: JPG, PNG, GIF and WebP. Maximum size: 5 MB.'
            ],
        ],
        'fields' => [
            'media_file' => [
                'type' => 'file',
                'label' => 'Image file',
                'required' => true,
                'sort_order' => 10,
                'config' => [
                    'accept' => 'image/jpeg,image/png,image/gif,image/webp'
                ]
            ],
        ],
    ],
];











