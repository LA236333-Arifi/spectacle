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
    // DashboardController
    case '/':
    case 'dashboard':
    case 'index.php':
    case 'index.html':
    case 'index':
        (new DashboardController())->index();
        break;

    // GET - Affiche la page de programmation
    case 'programmation':
        (new DashboardController())->programmation();
        break;
    
    // GET - Affiche la page du calendrier
    case 'calendrier':
        (new DashboardController())->calendrier();
        break;
    

    // AuthController
    // GET - Affiche la page d'inscription
    // POST - Gère l'inscription d'un utilisateur
    case 'register':
        (new AuthController())->register();
        break;

    // GET - Affiche la page de connexion
    // POST - Gère la connexion d'un utilisateur
    case 'login':
        (new AuthController())->login();
        break;

    // POST - Gère la déconnexion d'un utilisateur
    case 'logout':
        (new AuthController())->logout();
        break;

    // PasswordController
    case 'password':
        switch ($segments[1] ?? '')
        {
            // GET - Affiche la page de reset password
            case 'reset':
                (new PasswordController())->resetPassword();
                break;
            
            // POST - Change le mot de passe si le token est valide
            case 'change':
                (new PasswordController())->changePassword();
                break;
            default:
                $pageNonTrouvee = true;
                break;
        }
        break;

    // SpectacleController
    case 'spectacle':
        switch ($segments[1] ?? '')
        {
            // POST -- Ajoute un spectacle à la DB
            case 'add':
                (new SpectacleController())->ajouter();
                break;

            //POST -- Change les infos du spectacle, de l'auteur/metteur en scène, du groupe associé
            case 'modify':
                (new SpectacleController())->modifier();
                break;

            // POST -- Cloture un spectacle et annule toutes les séances programmées futures
            case 'cloturer':
                (new SpectacleController())->cloturer();
                break;

            // POST -- Supprime un spectacle, les séances, l'auteur/metteur en scène.
            // Mais la fonctionnalité est désactivée pour conserver l'intégrité des données
            /*case 'delete':
                (new SpectacleController())->supprimer();
                break;*/

            // GET -- Affiche les différents spectacles qui matchent la recherche
            case 'search':
                (new SpectacleController())->search();
                break;

            // GET -- Affiche la page des stats de chaque type de spectacle
            case 'stats':  
                (new SpectacleController())->stats();
                break;
            
            // GET -- Affiche la page d'information d'un spectacle
            case 'view':
                (new SpectacleController())->viewSpectacle();
                break;

            // GET -- Affiche la page pour télécharger le pdf
            // POST -- Visionne le PDF et permet de le télécharger  
            case 'print':
                if (isset($segments[2]))
                {
                    // Soit on spécifie tous les spectacles pour le PDF
                    if ($segments[2] == 'all')
                    {
                        (new PrintController())->printList();
                    }
                    else
                    {
                        $pageNonTrouvee = true;
                    }
                }
                else
                {
                    // Soit on laisse le chemin par défaut et si paramètre $_GET est set
                    // Alors on visionne le PDF de ce spectacle, sinon on affiche la page
                    (new PrintController())->index();
                }
                break;
            default:
                $pageNonTrouvee = true;
                break;
        }
        break;
    
    case 'seance':
        switch ($segments[1] ?? '')
        {
            // POST -- Ajoute une séance. Une "instance" d'un spectacle
            case 'add':
                (new SeanceController())->addSeance();
                break;

            // POST -- Déplacer une séance déjà existante à une autre date
            case 'move':
                (new SeanceController())->moveSeance();
                break;

            // POST -- Annule une séance
            case 'cancel':
                (new SeanceController())->cancelSeance();
                break;

            // GET -- Affiche la liste de toutes les prochaines séances 
            case 'list':
                (new SeanceController())->listSeances();
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
                switch($segments[2] ?? 'users')
                {
                    // GET - Affiche la liste des utilisateurs validés
                    case 'users':
                        (new UserController())->listUsers();
                        break;

                    // GET - Affiche la liste des utilisateurs non validés (en attente)
                    case 'access':
                        (new UserController())->listAccess();
                        break;
                    default:
                    $pageNonTrouvee = true;
                    break;
                }
                break;
            
            // GET - Recherche un utilisateur via son nom/prénom
            case 'search':
                (new UserController())->searchUsers();
                break;

            // POST - active/désactive un utilisateur en inversant son statut courant
            case 'toggle':
                (new UserController())->toggleStatus();
                break;
            default:
                $pageNonTrouvee = true;
                break;
        }
        break;

    default:
    $pageNonTrouvee = true;
    break;
}

// Si aucune route ne correspond, alors on affiche la fameuse erreur 404
if ($pageNonTrouvee)
{
    show404();
}
?>
