<?php

declare(strict_types=1);

return [
    ['id' => 'public', 'label' => 'Public', 'settings' => [['path' => 'example.value', 'label' => 'Value']]],
    ['id' => 'restricted', 'label' => 'Restricted', 'permission' => 'restricted.manage', 'settings' => [['path' => 'example.value', 'label' => 'Value']]],
];