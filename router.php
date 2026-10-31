<?php

$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$requestPath = rawurldecode($requestPath);
$normalizedPath = '/' . ltrim(str_replace('\\', '/', $requestPath), '/');
$normalizedPath = '/' . trim(preg_replace('#/+#', '/', $normalizedPath), '/');
if ($normalizedPath === '/') {
    $normalizedPath = '/';
}

$segments = array_values(array_filter(explode('/', trim($normalizedPath, '/')), 'strlen'));
$blockedDirectories = ['.git', '.aws', '.agents', '.codex', 'config', 'sql', 'tests', 'vendor', 'uploads'];
$blockedFiles = [
    '.gitignore',
    'README.md',
    'composer.json',
    'composer.lock',
    'package.json',
    'package-lock.json',
    'phpunit.xml',
    'router.php',
];
if ($segments && (in_array($segments[0], $blockedDirectories, true)
    || in_array($segments[0], $blockedFiles, true)
    || str_starts_with($segments[0], '.'))) {
    http_response_code(404);
    exit('Page introuvable.');
}

if ($normalizedPath === '/') {
    return false;
}

$resolvedPath = realpath(__DIR__ . $normalizedPath);
$projectRoot = realpath(__DIR__);
if ($resolvedPath !== false && $projectRoot !== false) {
    $prefix = $projectRoot . DIRECTORY_SEPARATOR;
    if (str_starts_with($resolvedPath, $prefix)) {
        $relativeSegments = explode(DIRECTORY_SEPARATOR, substr($resolvedPath, strlen($prefix)));
        if ($relativeSegments
            && (in_array($relativeSegments[0], $blockedDirectories, true)
                || in_array($relativeSegments[0], $blockedFiles, true)
                || str_starts_with($relativeSegments[0], '.'))) {
            http_response_code(404);
            exit('Page introuvable.');
        }

        if (is_file($resolvedPath)) {
            return false;
        }
    }
}

http_response_code(404);
header('Content-Type: text/plain; charset=utf-8');
echo 'Page introuvable.';
