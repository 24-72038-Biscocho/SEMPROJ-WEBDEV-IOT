<?php
declare(strict_types=1);

require __DIR__ . '/app/bootstrap.php';
require __DIR__ . '/routes/web.php';

$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
if (BASE_PATH !== '' && strncmp($requestPath, BASE_PATH, strlen(BASE_PATH)) === 0) {
    $requestPath = substr($requestPath, strlen(BASE_PATH)) ?: '/';
}

$router->dispatch($_SERVER['REQUEST_METHOD'] ?? 'GET', $requestPath);
