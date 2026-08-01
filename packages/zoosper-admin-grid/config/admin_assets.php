<?php

declare(strict_types=1);

return [
    'stylesheets' => [
        ['path' => 'resources/admin/css/grid-workspace.css', 'priority' => 70],
        ['path' => 'resources/admin/css/grid-compact-workspace.css', 'priority' => 80],
    ],
    'scripts' => [
        ['path' => 'resources/admin/js/grid-workspace.js', 'priority' => 70, 'defer' => true],
        ['path' => 'resources/admin/js/grid-compact-workspace.js', 'priority' => 80, 'defer' => true],
    ],
];
