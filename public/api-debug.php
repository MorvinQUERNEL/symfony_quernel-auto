<?php
/**
 * Script de diagnostic pour les problèmes de routing API sur Hostinger
 * Accéder via: https://quernelauto.fr/api-debug.php
 * ou: https://quernelauto.fr/app/public/api-debug.php
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Fonction de nettoyage identique à index.php
function cleanUri(string $uri): string {
    $prefixes = [
        '/app/public/index.php',
        '/app/public',
        '/public/index.php',
        '/public',
    ];

    foreach ($prefixes as $prefix) {
        if (str_starts_with($uri, $prefix)) {
            return substr($uri, strlen($prefix)) ?: '/';
        }
    }

    return $uri;
}

$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$redirectUrl = $_SERVER['REDIRECT_URL'] ?? null;

// Simuler la logique de index.php
$cleanedUri = $requestUri;
if (!empty($redirectUrl)) {
    $cleanedUri = cleanUri($redirectUrl);
} else {
    $cleanedUri = cleanUri($requestUri);
}

if (!str_starts_with($cleanedUri, '/')) {
    $cleanedUri = '/' . $cleanedUri;
}

echo json_encode([
    'diagnosis' => [
        'original_request_uri' => $requestUri,
        'redirect_url' => $redirectUrl,
        'cleaned_uri' => $cleanedUri,
        'expected_for_api' => '/api/vehicules',
    ],
    'server_variables' => [
        'REQUEST_URI' => $_SERVER['REQUEST_URI'] ?? null,
        'REDIRECT_URL' => $_SERVER['REDIRECT_URL'] ?? null,
        'SCRIPT_NAME' => $_SERVER['SCRIPT_NAME'] ?? null,
        'PHP_SELF' => $_SERVER['PHP_SELF'] ?? null,
        'PATH_INFO' => $_SERVER['PATH_INFO'] ?? null,
        'QUERY_STRING' => $_SERVER['QUERY_STRING'] ?? null,
        'DOCUMENT_ROOT' => $_SERVER['DOCUMENT_ROOT'] ?? null,
        'SCRIPT_FILENAME' => $_SERVER['SCRIPT_FILENAME'] ?? null,
        'REQUEST_METHOD' => $_SERVER['REQUEST_METHOD'] ?? null,
        'HTTP_HOST' => $_SERVER['HTTP_HOST'] ?? null,
    ],
    'htaccess_info' => [
        'REDIRECT_STATUS' => $_SERVER['REDIRECT_STATUS'] ?? null,
        'REDIRECT_QUERY_STRING' => $_SERVER['REDIRECT_QUERY_STRING'] ?? null,
    ],
    'php_info' => [
        'php_version' => PHP_VERSION,
        'sapi' => PHP_SAPI,
    ],
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
