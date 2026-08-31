<?php

declare(strict_types=1);

return [
    'key' => (string) env('CACHE_ENCRYPTION_KEY', ''),
    'cipher' => (string) env('CACHE_ENCRYPTION_CIPHER', 'aes-256-gcm'),
];








