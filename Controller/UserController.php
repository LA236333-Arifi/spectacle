<?php

/**
 * Contrôleur pour la gestion des utilisateurs
 * Gère les opérations CRUD sur les utilisateurs (administration)
 */
class UserController 
{
    private $security;
    private $emailSender;
    
    public function __construct() 
    {
        $this->security = new Security(true);
    }
    
    /**
     * Affiche la liste des utilisateurs (admin uniquement)
     * Route: GET /user/list
     */
    public function listUsers() 
    {
        if (!UserConnectionUtils::isAdminConnected()) 
        {
            http_response_code(400);
            ViewRenderer::error(new MessageErreur("Accès refusé", "Réservé aux administrateurs"));
            return false;
        }

        if (RequestUtils::isPostMethod() == false)
        {
            // Définir un code HTTP 405 (Method Not Allowed)
            http_response_code(405);

            // On setup le message d'erreur pour la vue
            ViewRenderer::error(new MessageErreur("Chargement de la page impossible", "Méthode non supportée"));
            return false;
        }

        $page = max(1, (int)($_GET['page'] ?? 1));
        $limit = max(UserList::LimitDefault, (int)$_GET['limit'] ?? UserList::LimitDefault);

        $userList = new UserList($page, $limit);

        // Récup des données stockées dans l'objet avec un cycle de vie limité à ce scope
        $userList->storeActiveUserList();

        $viewData = 
        [
            'users' => $userList->getUsers(),
            'totalPages' => $userList->getTotalPages(),
            'totalUsers' => $userList->getTotalUsers(),
            'currentPage' => $page
        ];

        // Affichage
        $viewRenderer = new ViewRenderer("View/UserViewList.php", $viewData);
        $viewRenderer->render();
    }

    public function listAccess()
    {
        if (!UserConnectionUtils::isAdminConnected())
        {
            http_response_code(400);
            ViewRenderer::error(new MessageErreur("Accès refusé", "Réservé aux administrateurs"));
            return false;
        }

        if (RequestUtils::isPostMethod() == false)
        {
            // Définir un code HTTP 405 (Method Not Allowed)
            http_response_code(405);

            // On setup le message d'erreur pour la vue
            ViewRenderer::error(new MessageErreur("Chargement de la page impossible", "Méthode non supportée"));
            return false;
        }

        $page = max(1, (int)($_GET['page'] ?? 1));
        $limit = max(UserList::LimitDefault, (int)($_GET['limit'] ?? UserList::LimitDefault));

        $userList = new UserList($page, $limit);
        $userList->storePendingValidationUserList();

        $viewData = 
        [
            'users' => $userList->getUsers(),
            'totalPages' => $userList->getTotalPages(),
            'totalUsers' => $userList->getTotalUsers(),
            'currentPage' => $page
        ];

        $viewRenderer = new ViewRenderer("View/UserAccessList.php", $viewData);
        $viewRenderer->render();
    }

    public function searchUsers()
    {
        if (!UserConnectionUtils::isAdminConnected())
        {
            http_response_code(403);
            ViewRenderer::error(new MessageErreur("Accès refusé", "Réservé aux administrateurs"));
            return false;
        }

        if (RequestUtils::isPostMethod() == false)
        {
            // Définir un code HTTP 405 (Method Not Allowed)
            http_response_code(405);

            // On setup le message d'erreur pour la vue
            ViewRenderer::error(new MessageErreur("Chargement de la page impossible", "Méthode non supportée"));
            return false;
        }

        header('Content-Type: application/json');

        $query = trim($_GET['query'] ?? '');
        $page = max(1, (int)($_GET['page'] ?? 1));
        $limit = max(UserList::LimitDefault, (int)($_GET['limit'] ?? UserList::LimitDefault));

        if (empty($query))
        {
            echo json_encode(
                [
                    'status' => 'error',
                    'message' => 'Champ de recherche vide.'
                ]);
                
            return false;
        }

        $userList = new UserList($page, $limit);
        $userList->searchUsersByQuery($query);

        echo json_encode(
        [
            'results' => $userList->getUsers(),
            'totalUsers' => $userList->getTotalUsers(),
            'totalPages' => $userList->getTotalPages(),
            'currentPage' => $page
        ]);
    }

    /**
     * Désactive/réactive un utilisateur
     * Route: POST /user/toggle
     */
    public function toggleStatus() 
    {
        if (!UserConnectionUtils::isAdminConnected()) 
        {
            // Définir un code HTTP 405 (Unauthorized)
            http_response_code(401);

            // On setup le message d'erreur pour la vue
            ViewRenderer::error(new MessageErreur("Chargement de la page impossible", "Il faut se connecter en tant qu'administrateur pour visionner cette page."));
            return false;
        }

        if (RequestUtils::isPostMethod() == false)
        {
            // Définir un code HTTP 405 (Method Not Allowed)
            http_response_code(405);

            // On setup le message d'erreur pour la vue
            ViewRenderer::error(new MessageErreur("Chargement de la page impossible", "Méthode non supportée"));
            return false;
        }
        
        header('Content-Type: application/json');

        // Définir un code HTTP 400 (Bad Request) par défaut 
        http_response_code(400);
        $userId = $_POST['id'] ?? null;

        if (empty($userId))
        {
            return false;
        }

        $userActivity = new UserActivity($userId);
        if ($userActivity->storeUserActivity() == false)
        {
            return false;
        }
        
        $result = $userActivity->toggleUserActivity();
        if ($result)
        {
            // Définir un code HTTP 200 (Succès)
            http_response_code(200);
            $newStatus = $userActivity->isUserStatutActif();
            $statusText = $newStatus ? 'activé' : 'désactivé';
            echo json_encode([
                'status' => "success",
                'message' => "Utilisateur " . $statusText . " avec succès",
                'actif' => $userActivity->getUserStatut()
            ]);

            return true;
        }
        else
        {
            // Définir un code HTTP 500 (Server Error)
            http_response_code(500);
            echo json_encode([
                    'status' => "error",
                    'message' => "Une erreur s'est produite. Veuillez réessayer plus tard."
                ]);
        }

        return false;
    }
} 