<?php

use App\Kernel;

// Fix pour Hostinger: corriger REQUEST_URI quand le document root n'est pas /public
// Structure Hostinger: public_html/app/public/
// Le .htaccess racine redirige /api/vehicules vers /app/public/api/vehicules
// On doit s'assurer que Symfony voit /api/vehicules (sans /app/public)

// Fonction pour nettoyer l'URI des préfixes de chemin
function cleanUri(string $uri): string {
    // Ordre important: vérifier les chemins les plus longs d'abord
    $prefixes = [
        '/app/public/index.php',  // 21 caractères
        '/app/public',            // 11 caractères
        '/public/index.php',      // 17 caractères
        '/public',                // 7 caractères
    ];

    foreach ($prefixes as $prefix) {
        if (str_starts_with($uri, $prefix)) {
            return substr($uri, strlen($prefix)) ?: '/';
        }
    }

    return $uri;
}

// Déterminer l'URI correcte
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';

// Si REDIRECT_URL existe, l'utiliser car c'est l'URL originale avant rewrite
if (!empty($_SERVER['REDIRECT_URL'])) {
    $requestUri = cleanUri($_SERVER['REDIRECT_URL']);
} else {
    $requestUri = cleanUri($requestUri);
}

// S'assurer que l'URI commence par /
if (!str_starts_with($requestUri, '/')) {
    $requestUri = '/' . $requestUri;
}

// Ajouter la query string si présente
if (!empty($_SERVER['QUERY_STRING']) && !str_contains($requestUri, '?')) {
    $requestUri .= '?' . $_SERVER['QUERY_STRING'];
}

$_SERVER['REQUEST_URI'] = $requestUri;

require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

return function (array $context) {
    return new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
};
