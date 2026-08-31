<?php

declare(strict_types=1);

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$path = is_string($path) ? $path : '/';
$publicRoot = realpath(__DIR__);
$candidate = realpath(__DIR__ . '/' . ltrim($path, '/'));

if (
    $path !== '/'
    && is_string($publicRoot)
    && is_string($candidate)
    && is_file($candidate)
    && str_starts_with($candidate, $publicRoot . DIRECTORY_SEPARATOR)
) {
    return false;
}

require __DIR__ . '/index.php';



