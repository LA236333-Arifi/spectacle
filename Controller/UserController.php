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
} 