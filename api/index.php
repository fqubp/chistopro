<?php
$path = $_GET['path'] ?? 'index.php';
$path = trim($path, '/');

if ($path === '') {
    $path = 'index.php';
}

if (str_contains($path, '..')) {
    http_response_code(400);
    exit('Bad request');
}

$root = realpath(__DIR__ . '/..');
$target = realpath($root . '/' . $path);

if (!$root || !$target || !str_starts_with($target, $root . DIRECTORY_SEPARATOR) || !is_file($target)) {
    http_response_code(404);
    exit('Not found');
}

if (pathinfo($target, PATHINFO_EXTENSION) !== 'php') {
    http_response_code(404);
    exit('Not found');
}

chdir(dirname($target));
require $target;
