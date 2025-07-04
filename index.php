<?php

require_once __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$env = $_ENV['APP_ENV'] ?? 'prod'; // fallback en cas d'oubli
if ($env === 'dev') 
{
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
} 
else 
{
    ini_set('display_errors', 0);
    error_reporting(0);
}

echo "Spectacle works!";
exit();

// Définir BASE_URL
define('BASE_URL', rtrim(dirname($_SERVER['SCRIPT_NAME']), '/'));

// ===================================
// ROUTAGE
// ===================================

// 1. Nettoyer l'URI
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// 2. Retirer correctement le dossier du projet
$scriptDir = dirname($_SERVER['SCRIPT_NAME']);

// Normaliser les slashs
$uri = '/' . ltrim($uri, '/');
$scriptDir = '/' . trim($scriptDir, '/');

// 3. Enlever le scriptDir si présent en début d'URI
if (stripos($uri, $scriptDir) === 0) 
{
    $uri = substr($uri, strlen($scriptDir));
}

// 4. Nettoyage final
$uri = trim($uri, '/');

// 5. Découpe
$segments = explode('/', $uri);

// Si pas d'URI spécifique, page d'accueil
if (empty($segments[0]) || $segments[0] === 'index.php') 
{
    require './View/AccueilConnexion.php';
    exit;
}

$pageNotFound = false;

// Routage basé sur segments
switch ($segments[0]) 
{
    default:
        $pageNotFound = true;
        break;
}

// Si on a pas une URL valide, alors on renvoie l'erreur 404
if ($pageNotFound)
{
    http_response_code(404);
    echo "Erreur 404 - Page non trouvée : " . $uri;
}
?>
