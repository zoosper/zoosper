<?php

declare(strict_types=1);

return [
    'admin.layout' => [
        'remove' => [
            // Example child theme usage: 'partials/footer.php',
        ],
        'replace' => [
            // Example child theme usage: 'partials/header.php' => 'partials/custom-header.php',
        ],
        'inject' => [
            // Example child theme usage: 'before.content' => ['partials/announcement.php'],
        ],
    ],
    'admin.content' => [
        'remove' => [],
        'replace' => [],
        'inject' => [],
    ],
];
