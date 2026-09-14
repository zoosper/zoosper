<?php

declare(strict_types=1);

use Zoosper\StoreOrders\StoreOrderIntegrationGate;

$integration = StoreOrderIntegrationGate::configuration();

return [
    'enabled' => $integration['enabled'],
    'api_base_url' => $integration['api_base_url'],
    'store_code' => $integration['store_code'],
    'kiosk_website_id' => $integration['kiosk_website_id'],
    'connect_timeout_ms' => (int) env('STORE_ORDERS_CONNECT_TIMEOUT_MS', 1000),
    'request_timeout_ms' => (int) env('STORE_ORDERS_REQUEST_TIMEOUT_MS', 5000),
    'maximum_response_bytes' => (int) env('STORE_ORDERS_MAXIMUM_RESPONSE_BYTES', 2000000),
];
