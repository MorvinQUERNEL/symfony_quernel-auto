<?php

use App\Kernel;

// Fix pour Hostinger: corriger REQUEST_URI quand le document root n'est pas /public
// Quand on accède à /api/vehicules, le serveur peut avoir:
// - REDIRECT_URL = /api/vehicules (l'URL originale avant rewrite)
// - REQUEST_URI = /public/index.php/api/vehicules ou /public/api/vehicules
// On doit s'assurer que Symfony voit /api/vehicules
if (!empty($_SERVER['REDIRECT_URL'])) {
    // REDIRECT_URL contient l'URL originale demandée
    $originalUrl = $_SERVER['REDIRECT_URL'];
    // Retirer /public du début si présent
    if (str_starts_with($originalUrl, '/public')) {
        $originalUrl = substr($originalUrl, 7) ?: '/';
    }
    $_SERVER['REQUEST_URI'] = $originalUrl;
    if (!empty($_SERVER['QUERY_STRING'])) {
        $_SERVER['REQUEST_URI'] .= '?' . $_SERVER['QUERY_STRING'];
    }
} elseif (!empty($_SERVER['REQUEST_URI'])) {
    // Fallback: nettoyer REQUEST_URI si elle contient /public
    $uri = $_SERVER['REQUEST_URI'];
    if (str_starts_with($uri, '/public/index.php')) {
        $_SERVER['REQUEST_URI'] = substr($uri, 17) ?: '/';
    } elseif (str_starts_with($uri, '/public')) {
        $_SERVER['REQUEST_URI'] = substr($uri, 7) ?: '/';
    }
}

require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

return function (array $context) {
    return new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
};
