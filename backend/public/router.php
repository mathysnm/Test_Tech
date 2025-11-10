<?php

/**
 * Router pour le serveur PHP intégré
 * Gère correctement les query parameters et redirige vers index.php
 */

// Extraire les query parameters de REQUEST_URI
$uri = parse_url($_SERVER['REQUEST_URI']);
$path = $uri['path'] ?? '/';
$query = $uri['query'] ?? '';

// Mettre à jour $_GET avec les query params
if ($query) {
    parse_str($query, $_GET);
    $_SERVER['QUERY_STRING'] = $query;
}

// Si le fichier existe (assets statiques), le servir directement
if ($path !== '/' && file_exists(__DIR__ . $path)) {
    return false;
}

// Sinon, déléguer à index.php
$_SERVER['SCRIPT_NAME'] = '/index.php';
$_SERVER['SCRIPT_FILENAME'] = __DIR__ . '/index.php';
require __DIR__ . '/index.php';
