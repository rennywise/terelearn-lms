<?php

function terelearn_load_root_page(string $routeKey): void
{
    $root = dirname(__DIR__, 2);
    $routes = require __DIR__ . '/../config/root_pages.php';

    if (!isset($routes[$routeKey])) {
        http_response_code(404);
        exit('Page route not found.');
    }

    $relativePath = $routes[$routeKey];
    $targetPath = $root . '/' . $relativePath;
    $resolvedPath = realpath($targetPath);
    $pagesRoot = realpath($root . '/app/pages');

    if ($resolvedPath === false || $pagesRoot === false || strncmp($resolvedPath, $pagesRoot, strlen($pagesRoot)) !== 0) {
        http_response_code(500);
        exit('Invalid page route configuration.');
    }

    require $resolvedPath;
}
