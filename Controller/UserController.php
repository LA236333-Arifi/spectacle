<?php

require_once 'autoload.php';

/**
 * Contrôleur pour la gestion des utilisateurs
 * Gère les opérations CRUD sur les utilisateurs (administration)
 */
class UserController 
{
    private $security;
    
    public function __construct() 
    {
        $this->security = new Security(true);
    }
    
    /**
     * Affiche la liste des utilisateurs validés (admin uniquement)
     * Route: GET /user/list
     */
    public function listUsers() 
    {
        if (!UserConnectionUtils::isAdminConnected()) 
        {
            http_response_code(403);
            ViewRenderer::error(new MessageErreur("Accès refusé", "Réservé aux administrateurs"));
            return false;
        }

        if (RequestUtils::isGetMethod() == false)
        {
            // Définir un code HTTP 405 (Method Not Allowed)
            http_response_code(405);

            // On setup le message d'erreur pour la vue
            ViewRenderer::error(new MessageErreur("Chargement de la page impossible", "Méthode non supportée"));
            return false;
        }

        $viewData = 
        [
            'userStatutsJson' => json_encode(UserStatut::getUserStatutToString()),
            'token_csrf' => $this->security->genererCSRFToken()
        ];

        // Affichage
        $viewRenderer = new ViewRenderer("View/Gerant/ListeUsersValides.php", $viewData);
        $viewRenderer->render();

        return true;
    }

    public function apiListUsers()
    {
        if (!UserConnectionUtils::isAdminConnected()) 
        {
            http_response_code(403);
            echo json_encode(
                [
                    'status' => 'error',
                    'message' => "Réservé aux administrateurs."
                ]);
            return false;
        }

        if (RequestUtils::isGetMethod() == false)
        {
            // Définir un code HTTP 405 (Method Not Allowed)
            http_response_code(405);

            // On setup le message d'erreur pour la vue
            echo json_encode(
                [
                    'status' => 'error',
                    'message' => "Méthode non supportée."
                ]);
            
            return false;
        }

        $page = max(1, ($_GET['page'] ?? 1));
        $limit = $_GET['limit'] ?? UserList::LimitDefault;

        $page = filter_var($page, FILTER_VALIDATE_INT);
        $limit = filter_var($limit, FILTER_VALIDATE_INT);

        if ($page === false || $limit === false)
        {
            echo json_encode(
                [
                    'status' => 'error',
                    'message' => "Les paramètres de la page et de la limite doivent être numériques, si présents."
                ]);
            
            return false;
        }

        $userId = UserConnectionUtils::getConnectedUserId();
        $userList = new UserList($page, $limit, $userId);

        // Récup des données stockées dans l'objet avec un cycle de vie limité à ce scope
        $userList->storeActiveUserList();

        echo json_encode
        ([
            'status'        => 'success', 
            'users'         => $userList->getUsers(),
            'totalPages'    => $userList->getTotalPages(),
            'totalUsers'    => $userList->getTotalUsers(),
            'currentPage'   => $page
        ]);
        return true;
    }

    public function apiListAccess()
    {
        if (!UserConnectionUtils::isAdminConnected()) 
        {
            http_response_code(403);
            echo json_encode(
                [
                    'status' => 'error',
                    'message' => "Réservé aux administrateurs."
                ]);
            return false;
        }

        if (RequestUtils::isGetMethod() == false)
        {
            // Définir un code HTTP 405 (Method Not Allowed)
            http_response_code(405);

            // On setup le message d'erreur pour la vue
            echo json_encode(
                [
                    'status' => 'error',
                    'message' => "Méthode non supportée."
                ]);
            
            return false;
        }

        $page = max(1, ($_GET['page'] ?? 1));
        $limit = $_GET['limit'] ?? UserList::LimitDefault;

        $page = filter_var($page, FILTER_VALIDATE_INT);
        $limit = filter_var($limit, FILTER_VALIDATE_INT);

        if ($page === false || $limit === false)
        {
            echo json_encode(
                [
                    'status' => 'error',
                    'message' => "Les paramètres de la page et de la limite doivent être numériques, si présents."
                ]);
            
            return false;
        }

        $userId = UserConnectionUtils::getConnectedUserId();
        $userList = new UserList($page, $limit, $userId);

        // Récup des données stockées dans l'objet avec un cycle de vie limité à ce scope
        $userList->storePendingValidationUserList();

        echo json_encode
        ([
            'status'        => 'success', 
            'users'         => $userList->getUsers(),
            'totalPages'    => $userList->getTotalPages(),
            'totalUsers'    => $userList->getTotalUsers(),
            'currentPage'   => $page
        ]);
        return true;
    }

    public function listAccess()
    {
        if (!UserConnectionUtils::isAdminConnected())
        {
            http_response_code(400);
            ViewRenderer::error(new MessageErreur("Accès refusé", "Réservé aux administrateurs"));
            return false;
        }

        if (RequestUtils::isGetMethod() == false)
        {
            // Définir un code HTTP 405 (Method Not Allowed)
            http_response_code(405);

            // On setup le message d'erreur pour la vue
            ViewRenderer::error(new MessageErreur("Chargement de la page impossible", "Méthode non supportée"));
            return false;
        }

        $viewData = 
        [
            'userStatutsJson' => json_encode(UserStatut::getUserStatutToString()),
            'token_csrf' => $this->security->genererCSRFToken()
        ];

        $viewRenderer = new ViewRenderer("View/Gerant/ListeUsersAttente.php", $viewData);
        $viewRenderer->render();

        return true;
    }

    /**
     * Désactive/réactive un utilisateur
     * Route: POST /user/toggle
     */
    public function toggleStatus() 
    {
        header('Content-Type: application/json');

        if (!UserConnectionUtils::isAdminConnected()) 
        {
            // Définir un code HTTP 405 (Unauthorized)
            http_response_code(401);

            // On setup le message d'erreur pour la vue
            echo json_encode(
                [
                    'status' => 'error',
                    'message' => "Il faut se connecter en tant qu'administrateur pour utiliser ce endpoint"
                ]);
                
            return false;
        }

        if (RequestUtils::isPostMethod() == false)
        {
            // Définir un code HTTP 405 (Method Not Allowed)
            http_response_code(405);

            // On setup le message d'erreur pour la vue
            echo json_encode(
                [
                    'status' => 'error',
                    'message' => "La méthode n'est pas supportée, veuillez utiliser du GET"
                ]);
            return false;
        }

        // Vérification du token CSRF
        if (!$this->security->checkCSRFToken())
        {
            http_response_code(403);
            echo json_encode([
                'status' => 'error',
                'message' => "Token CSRF invalide."
            ]);
            return false;
        }
        
        // Définir un code HTTP 400 (Bad Request) par défaut 
        http_response_code(400);
        $userId = $_POST['id'] ?? null;

        if (empty($userId))
        {
            echo json_encode(
                [
                    'status' => 'error',
                    'message' => "Le paramètre id de l'utilisateur est absent."
                ]);
            return false;
        }

        $userId = filter_var($userId, FILTER_VALIDATE_INT);
        if ($userId === false)
        {
            return false;
        }

        if ($userId == UserConnectionUtils::getConnectedUserId())
        {
            return false;
        }

        $userActivity = new UserActivity($userId);
        if ($userActivity->storeUserActivity() == false)
        {
            echo json_encode(
                [
                    'status' => 'error',
                    'message' => "L'utilisateur n'a pas été trouvé."
                ]);
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

    public function accept()
    {
        header('Content-Type: application/json');

        if (!UserConnectionUtils::isAdminConnected()) 
        {
            // Définir un code HTTP 405 (Unauthorized)
            http_response_code(401);

            // On setup le message d'erreur pour la vue
            echo json_encode(
                [
                    'status' => 'error',
                    'message' => "Il faut se connecter en tant qu'administrateur pour utiliser ce endpoint"
                ]);
                
            return false;
        }

        if (RequestUtils::isPostMethod() == false)
        {
            // Définir un code HTTP 405 (Method Not Allowed)
            http_response_code(405);

            // On setup le message d'erreur pour la vue
            echo json_encode(
                [
                    'status' => 'error',
                    'message' => "La méthode n'est pas supportée, veuillez utiliser du GET"
                ]);
            return false;
        }

        // Vérification du token CSRF
        if (!$this->security->checkCSRFToken())
        {
            http_response_code(403);
            echo json_encode([
                'status' => 'error',
                'message' => "Token CSRF invalide."
            ]);
            return false;
        }

        // Définir un code HTTP 400 (Bad Request) par défaut 
        http_response_code(400);
        $userId = $_POST['id'] ?? null;

        if (empty($userId))
        {
            echo json_encode(
                [
                    'status' => 'error',
                    'message' => "Le paramètre id de l'utilisateur est absent."
                ]);
            return false;
        }

        $userId = filter_var($userId, FILTER_VALIDATE_INT);
        if ($userId === false)
        {
            echo json_encode(
                [
                    'status' => 'error',
                    'message' => "Le paramètre id de l'utilisateur est incorrect."
                ]);
            return false;
        }

        $userActivity = new UserActivity($userId);
        if ($userActivity->acceptUser() == false)
        {
            echo json_encode(
                [
                    'status' => 'error',
                    'message' => "L'acceptation de l'utilisateur a échoué. Vérifiez qu'il s'agit un d'utilisateur valide."
                ]);
            return false;   
        }

        $email = $userActivity->getUserEmail();
        if ($email === false)
        {
            echo json_encode(
                [
                    'status' => 'error',
                    'message' => "Le paramètre id de l'utilisateur est introuvable."
                ]);
        }

        // On setup les données de l'email à envoyer
        $emailSender = new EmailSender($email);
        $emailSender->setSubject(AcceptInscriptionEmail::getSubject([]));
        $emailSender->setBody(AcceptInscriptionEmail::getEmailContent([]));

        // Envoi de l'email et récupère le résultat
        $emailSent = $emailSender->sendMail();

        http_response_code(200);
        echo json_encode
        ([
            'status' => 'success',
            'message' => "L'utilisateur a bien été accepté.",
            'emailEnvoye' => $emailSent
        ]);

        return true;
    }

    public function refuse()
    {
        header('Content-Type: application/json');

        if (!UserConnectionUtils::isAdminConnected()) 
        {
            // Définir un code HTTP 405 (Unauthorized)
            http_response_code(401);

            // On setup le message d'erreur pour la vue
            echo json_encode(
                [
                    'status' => 'error',
                    'message' => "Il faut se connecter en tant qu'administrateur pour utiliser ce endpoint"
                ]);
                
            return false;
        }

        if (RequestUtils::isPostMethod() == false)
        {
            // Définir un code HTTP 405 (Method Not Allowed)
            http_response_code(405);

            // On setup le message d'erreur pour la vue
            echo json_encode(
                [
                    'status' => 'error',
                    'message' => "La méthode n'est pas supportée, veuillez utiliser du GET"
                ]);
            return false;
        }

        // Vérification du token CSRF
        if (!$this->security->checkCSRFToken())
        {
            http_response_code(403);
            echo json_encode([
                'status' => 'error',
                'message' => "Token CSRF invalide."
            ]);
            return false;
        }

        // Définir un code HTTP 400 (Bad Request) par défaut 
        http_response_code(400);
        $userId = $_POST['id'] ?? null;

        if (empty($userId))
        {
            echo json_encode(
                [
                    'status' => 'error',
                    'message' => "Le paramètre id de l'utilisateur est absent."
                ]);
            return false;
        }

        $userId = filter_var($userId, FILTER_VALIDATE_INT);
        if ($userId === false)
        {
            echo json_encode(
                [
                    'status' => 'error',
                    'message' => "Le paramètre id de l'utilisateur est incorrect."
                ]);
            return false;
        }

        $userActivity = new UserActivity($userId);

        // On récupère d'abord l'email avant de supprimer l'user de la DB
        $email = $userActivity->getUserEmail();
        if ($email === false)
        {
            echo json_encode(
                [
                    'status' => 'error',
                    'message' => "Le paramètre id de l'utilisateur est introuvable."
                ]);
            return false;
        }

        // On setup les infos du mail à envoyer
        $emailSender = new EmailSender($email);
        $emailSender->setSubject(RejectInscriptionEmail::getSubject([]));
        $emailSender->setBody(RejectInscriptionEmail::getEmailContent([]));

        // On envoie le mail
        $emailSent = $emailSender->sendMail();

        // On peut refuser l'inscription maintenant et donc supprimer le user de la DB
        if ($userActivity->refuseUser() == false)
        {
            echo json_encode(
                [
                    'status' => 'error',
                    'message' => "Le refus de l'utilisateur a échoué. Vérifiez qu'il s'agit un d'utilisateur valide."
                ]);

            return false;   
        }

        http_response_code(200);
        echo json_encode
        ([
            'status' => 'success',
            'message' => "L'utilisateur a bien été accepté.",
            'emailEnvoye' => $emailSent
        ]);

        return true;
    }
} 