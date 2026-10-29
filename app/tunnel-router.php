<?php
declare(strict_types=1);

$projectRoot = dirname(__DIR__);
$basePath = '/WEBDEV_IOT_SEM_PROJ';
$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';

if ($requestPath === $basePath) {
    $appPath = '/';
} elseif (strncmp($requestPath, $basePath . '/', strlen($basePath) + 1) === 0) {
    $appPath = substr($requestPath, strlen($basePath));
} else {
    http_response_code(404);
    exit('404 - Page not found');
}

if (strncmp($appPath, '/public/', 8) === 0) {
    $publicRoot = realpath($projectRoot . '/public');
    $assetPath = realpath($projectRoot . $appPath);

    if (
        $publicRoot === false
        || $assetPath === false
        || !is_file($assetPath)
        || strncmp($assetPath, $publicRoot . DIRECTORY_SEPARATOR, strlen($publicRoot . DIRECTORY_SEPARATOR)) !== 0
    ) {
        http_response_code(404);
        exit('404 - Asset not found');
    }

    $mimeTypes = [
        'css' => 'text/css; charset=UTF-8',
        'js' => 'application/javascript; charset=UTF-8',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'svg' => 'image/svg+xml'
    ];
    $extension = strtolower(pathinfo($assetPath, PATHINFO_EXTENSION));
    $mimeType = $mimeTypes[$extension] ?? null;
    if ($mimeType === null) {
        http_response_code(415);
        exit('415 - Asset type not allowed');
    }

    header('Content-Type: ' . $mimeType);
    header('X-Content-Type-Options: nosniff');
    readfile($assetPath);
    exit;
}

$_SERVER['SCRIPT_NAME'] = $basePath . '/index.php';
$_SERVER['SCRIPT_FILENAME'] = $projectRoot . '/index.php';
$_SERVER['PHP_SELF'] = $basePath . '/index.php';

require $projectRoot . '/index.php';
