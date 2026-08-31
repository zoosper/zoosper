<?php

declare(strict_types=1);

return [
    'api_base_url' => (string) env('STORE_ORDERS_API_BASE_URL', 'http://127.0.0.1:3000'),
    'connect_timeout_ms' => (int) env('STORE_ORDERS_CONNECT_TIMEOUT_MS', 1000),
    'request_timeout_ms' => (int) env('STORE_ORDERS_REQUEST_TIMEOUT_MS', 5000),
    'maximum_response_bytes' => (int) env('STORE_ORDERS_MAXIMUM_RESPONSE_BYTES', 2000000),
];











