<?php
require_once __DIR__ . '/vendor/autoload.php';

// Chargement des variables d'environnement
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Configuration de l'environnement
$env = $_ENV['APP_ENV'] ?? 'prod';
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

// Autoloading des classes
spl_autoload_register(function ($class) {
    $paths = 
    [
        __DIR__ . '/Controller/'
    ];
    
    foreach ($paths as $path) {
        $file = $path . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// Configuration des constantes principales
define('SITE_NAME', 'Salle de Spectacle');
define('SITE_URL', $_ENV['SITE_URL'] ?? 'localhost');

/**
 * Affiche une page 404
 */
function show404() 
{
    // Affiche une page 404 HTML
    http_response_code(404);
    echo "<h1>Erreur 404</h1><p>Page non trouvée</p>";
}

// ===================================
// ROUTAGE PRINCIPAL
// ===================================

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
if (stripos($uri, $scriptDir) === 0) {
    $uri = substr($uri, strlen($scriptDir));
}

// 4. Nettoyage final
$uri = trim($uri, '/');

// 5. Découpe
$segments = explode('/', $uri);

$pageNonTrouvee = false;

switch ($segments[0] ?? '/') 
{
    case '/':
    case 'index.php':
    case 'index.html':
        break;

    case '/dashboard':
        break;

    case 'login':
        break;

    case 'register':
        break;
    case '/logout':
        break;

    case 'password':
        switch ($segments[1] ?? '')
        {
            case 'reset':
                break;
            case 'change':
                break;
            default:
                $pageNonTrouvee = true;
                break;
        }
        break;

    case 'spectacle':
        switch ($segments[1] ?? '')
        {
            case 'add':
                break;
            case 'move':
                break;
            case 'delete':
                break;
            case 'list':
                break;
            default:
                $pageNonTrouvee = true;
                break;
        }
        break;
    
    case 'seance':
        switch ($segments[1] ?? '')
        {
            case 'list':
                break;
            case 'calendar':
                break;
            case 'add':
                break;
            default:
                $pageNonTrouvee = true;
                break;
        }
        break;

    case 'user':
        switch ($segments[1] ?? '')
        {
            case 'list':
                break;
            case 'profil':

            default:
                break;
        }
        break;
}

// Si aucune route ne correspond, alors on affiche la fameuse erreur 404
if ($pageNonTrouvee)
{
    show404();
}
?>
