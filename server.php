<?php

$publicPath = getcwd();

$uri = urldecode(
    parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? ''
);

$filePath = $publicPath.$uri;

// If the file exists, serve it with proper Cache-Control headers
if ($uri !== '/' && file_exists($filePath) && ! is_dir($filePath)) {
    $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
    $mimeTypes = [
        'css' => 'text/css',
        'js' => 'application/javascript',
        'json' => 'application/json',
        'png' => 'image/png',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'gif' => 'image/gif',
        'svg' => 'image/svg+xml',
        'ico' => 'image/x-icon',
        'woff' => 'font/woff',
        'woff2' => 'font/woff2',
        'ttf' => 'font/ttf',
        'otf' => 'font/otf',
        'webp' => 'image/webp',
        'avif' => 'image/avif',
    ];

    $mimeType = $mimeTypes[$extension] ?? 'application/octet-stream';
    header("Content-Type: $mimeType");

    // Set Cache-Control headers
    if (str_starts_with($uri, '/build/assets/')) {
        header('Cache-Control: public, max-age=31536000, immutable');
    } elseif (str_starts_with($uri, '/asset/') || str_starts_with($uri, '/fonts/')) {
        header('Cache-Control: public, max-age=2592000, must-revalidate');
    } else {
        header('Cache-Control: public, max-age=86400'); // 1 day default for other static files
    }

    readfile($filePath);
    exit;
}

$formattedDateTime = date('D M j H:i:s Y');
$requestMethod = $_SERVER['REQUEST_METHOD'];
$remoteAddress = $_SERVER['REMOTE_ADDR'].':'.$_SERVER['REMOTE_PORT'];

file_put_contents('php://stdout', "[$formattedDateTime] $remoteAddress [$requestMethod] URI: $uri\n");

require_once $publicPath.'/index.php';
