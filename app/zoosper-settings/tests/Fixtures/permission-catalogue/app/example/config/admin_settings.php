<?php

declare(strict_types=1);

return [
    ['id' => 'public', 'label' => 'Public', 'settings' => [['path' => 'example.public', 'label' => 'Public']]],
    ['id' => 'restricted', 'label' => 'Restricted', 'permission' => 'restricted.manage', 'settings' => [['path' => 'example.restricted', 'label' => 'Restricted']]],
];