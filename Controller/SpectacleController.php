<?php

require_once 'autoload.php';

class SpectacleController
{
    private $security;

    public function __construct()
    {
        $this->security = new Security(true);
    }

    public function indexAjouter()
    {
        if (RequestUtils::isGetMethod() == false)
        {
            // Définir un code HTTP 405 (Method Not Allowed)
            http_response_code(405);
            ViewRenderer::error(new MessageErreur("Chargement de la page impossible", "Méthode non supportée"));
            return false;
        }

        if (UserConnectionUtils::isAdminConnected() == false)
        {
            http_response_code(400);
            ViewRenderer::error(new MessageErreur("Chargement de la page impossible", "Il faut être connecté en tant qu'admin pour visualiser cette page."));
            return false;
        }

        $viewData = 
        [
            'token_csrf'            => $this->security->genererCSRFToken(),
            'types'                 => SpectacleType::getSpectacleTypeToString()
        ];

        $viewRenderer = new ViewRenderer("View/Gerant/AjouterSpectacle.php", $viewData);
        $viewRenderer->render();
    }

    public function indexCloturer()
    {
        if (RequestUtils::isGetMethod() == false)
        {
            // Définir un code HTTP 405 (Method Not Allowed)
            http_response_code(405);
            ViewRenderer::error(new MessageErreur("Chargement de la page impossible", "Méthode non supportée"));
            return false;
        }

        if (UserConnectionUtils::isAdminConnected() == false)
        {
            http_response_code(400);
            ViewRenderer::error(new MessageErreur("Chargement de la page impossible", "Il faut être connecté en tant qu'admin pour visualiser cette page."));
            return false;
        }

        $viewData = 
        [
            'token_csrf'            => $this->security->genererCSRFToken(),
            'types'                 => SpectacleType::getSpectacleTypeToString()
        ];

        $viewRenderer = new ViewRenderer("View/Gerant/CloturerSpectacle.php", $viewData);
        $viewRenderer->render();
    }

    public function ajouter()
    {
        header('Content-Type: application/json');

        if (!UserConnectionUtils::isAdminConnected()) 
        {
            // Définir un code HTTP 405 (Unauthorized)
            http_response_code(405);

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
            // Définir un code HTTP 403 (Method Not Allowed)
            http_response_code(403);

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

        http_response_code(400);

        $data = $_POST;
        $data['utilisateur_id'] = UserConnectionUtils::getConnectedUserId();
        
        $spectacle = new Spectacle();
        if ($spectacle->addSpectacle($data) == false)
        {
            $errors = $spectacle->getErrors();
            echo json_encode([
                    'status' => 'error',
                    'message' => "Le spectacle n'a pas pu être ajouté pour ces raisons: " . implode(", ", $errors)
                ]);

            return false;
        }

        http_response_code(200);
        echo json_encode([
            'status' => 'success',
            'message' => "Le spectacle a été ajouté avec succès."
        ]);

        return true;
    }

    public function cloturer()
    {
        header('Content-Type: application/json');

        if (!UserConnectionUtils::isAdminConnected()) 
        {
            // Définir un code HTTP 405 (Unauthorized)
            http_response_code(405);

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
            // Définir un code HTTP 403 (Method Not Allowed)
            http_response_code(403);

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

        http_response_code(400);

        $spectacleId = $_POST['spectacle_id'] ?? null;
        if (empty($spectacleId))
        {
            echo json_encode([
                'status' => 'error',
                'message' => "Le spectacle_id est absent."
            ]);
            return false;
        }

        $spectacleId = filter_var($spectacleId, FILTER_VALIDATE_INT);
        if ($spectacleId === false)
        {
            echo json_encode([
                'status' => 'error',
                'message' => "Le spectacle_id doit être au format numérique"
            ]);

            return false;
        }

        $spectacle = new Spectacle($spectacleId);
        if ($spectacle->cloturerSpectacle() == false)
        {
            echo json_encode([
                    'status' => 'error',
                    'message' => "Le spectacle n'a pas pu être cloturé."
                ]);
            return false;
        }

        http_response_code(200);
        echo json_encode([
            'status' => 'success',
            'message' => "Le spectacle a été cloturé avec succès."
        ]);

        return true;
    }
    
    /**
     * Affiche la page des stats pour les admins
     */
    public function statsPage()
    {
        if (UserConnectionUtils::isAdminConnected() == false)
        {
            http_response_code(403);
            ViewRenderer::error(new MessageErreur("Chargement de la page impossible", "Il faut être connecté en tant qu'admin pour visualiser cette page."));
            return false;
        }

        if (RequestUtils::isGetMethod() == false)
        {
            http_response_code(405);
            ViewRenderer::error(new MessageErreur("Chargement de la page impossible", "Méthode non supportée."));
            return false;
        }

        $viewRenderer = new ViewRenderer("View/Visitor/RepartitionType.php", []);
        $viewRenderer->render();

        return true;
    }

    public function apiStats()
    {
        header('Content-Type: application/json');

        if (UserConnectionUtils::isAdminConnected() == false)
        {
            http_response_code(403);
            echo json_encode
            ([
                'status' => 'error',
                'message' => "Il faut être administrateur pour récupérer ces données",
            ]);
            return false;
        }

        if (RequestUtils::isGetMethod() == false)
        {
            http_response_code(405);
            echo json_encode
            ([
                'status' => 'error',
                'message' => "La méthode n'est pas supportée. Il faut utiliser GET",
            ]);
            return false;
        }

        $year = $_GET['year'] ?? null;
        if (empty($year)) 
        {
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => "Le paramètre 'date' est manquant (format DD-MM-YYYY attendu)",
            ]);
            return false;
        }

        $year = filter_var($year, FILTER_VALIDATE_INT);
        if ($year === false)
        {
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => "Le paramètre 'year' est dans un mauvais format (format: Integer)"
            ]);
            return false;
        }

        
        if (DateUtils::isYearInLimit($year) == false)
        {
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => "Le paramètre 'year' ne peut prendre que les valeurs suivantes: 2025, 2026"
            ]);
            return false;
        }

        $statsModel = new SpectacleStats($year);
        $stats = $statsModel->getStatsParType();

        echo json_encode
            ([
                'status' => 'success',
                'message' => "Les statistiques ont été récupérées avec succès",
                'stats' => $stats
            ]);

        return true;
    }

    public function apiCalendrier()
    {
        header('Content-Type: application/json');

        if (UserConnectionUtils::isAdminConnected() == false && false)
        {
            http_response_code(403);
            echo json_encode
            ([
                'status' => 'error',
                'message' => "Il faut être administrateur pour récupérer ces données",
            ]);
            return false;
        }

        if (RequestUtils::isGetMethod() == false)
        {
            http_response_code(405);
            echo json_encode
            ([
                'status' => 'error',
                'message' => "La méthode n'est pas supportée. Il faut utiliser GET",
            ]);
            return false;
        }

        $year = $_GET['year'] ?? null;
        if (empty($year)) 
        {
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => "Le paramètre 'date' est manquant (format DD-MM-YYYY attendu)",
            ]);
            return false;
        }

        $year = filter_var($year, FILTER_VALIDATE_INT);
        if ($year === false)
        {
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => "Le paramètre 'year' est dans un mauvais format (format: Integer)"
            ]);
            return false;
        }

        if (DateUtils::isYearInLimit($year) == false)
        {
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => "Le paramètre 'year' ne peut prendre que les valeurs suivantes: 2025, 2026"
            ]);
            return false;
        }

        $statsModel = new SpectacleStats($year);
        $stats = $statsModel->getStatsParMois();

        echo json_encode
        ([
            'status' => 'success',
            'message' => "Les statistiques ont été récupérées avec succès",
            'stats' => $stats
        ]);

        return true;
    }

    /**
     * Permet de visualiser les informations d'un spectacle
     */
    public function apiViewSpectacle()
    {
        if (RequestUtils::isGetMethod() == false)
        {
            http_response_code(405);
            echo json_encode
            ([
                'status' => 'error',
                'message' => "Seule la méthode GET est supportée",
            ]);
            return false;
        }

        http_response_code(400);

        $spectacleId = $_GET['id'] ?? null;
        $spectacleId = filter_var($spectacleId, FILTER_VALIDATE_INT);

        if (empty($spectacleId) || $spectacleId === false)
        {
            echo json_encode
            ([
                'status' => 'error',
                'message' => "L'id du spectacle est manquant ou n'est pas numérique",
            ]);
            return false;
        }

        $viewer = new SpectacleViewer($spectacleId);
        $spectacleData = $viewer->getSpectacleData();

        if (!$spectacleData)
        {
            echo json_encode
            ([
                'status' => 'error',
                'message' => "Les informations du spectacle n'ont pas pu être récupérées",
            ]);
            return false;
        }
        
        http_response_code(200);

        echo json_encode
        ([
            'status' => 'success',
            'message' => "Les informations du spectacle ont été récupérées avec succès",
            'spectacle' => $spectacleData
        ]);

        return true;
    }

    public function viewSpectaclePage()
    {
        if (RequestUtils::isGetMethod() == false)
        {
            http_response_code(405);
            ViewRenderer::error(new MessageErreur("Chargement de la page impossible", "Méthode non supportée"));
            return false;
        }

        http_response_code(400);

        $spectacleId = $_GET['id'] ?? null;
        $spectacleId = filter_var($spectacleId, FILTER_VALIDATE_INT);

        if ($spectacleId === false)
        {
            ViewRenderer::error(new MessageErreur("Chargement de la page impossible", "Identifiant du spectacle invalide."));
            return false;
        }

        http_response_code(200);

        $viewData = ['spectacle_id' => $spectacleId];
        $viewRenderer = new ViewRenderer("View/Visitor/SpectacleView.php", $viewData);
        $viewRenderer->render();

        return true;
    }

    public function searchPage()
    {
        if (RequestUtils::isGetMethod() == false)
        {
            http_response_code(405);
            ViewRenderer::error(new MessageErreur("Chargement de la page impossible", "Méthode non supportée"));
            return false;
        }

        $viewRenderer = new ViewRenderer("View/Visitor/SpectacleSearchList.php", []);
        $viewRenderer->render();

        return true;
    }

    /**
     * Permet d'avoir la liste des spectacles qui conviennent à une recherche
     */
    public function apiSearch()
    {
        header('Content-Type: application/json');

        if (RequestUtils::isGetMethod() == false)
        {
            http_response_code(405);
            echo json_encode([
                'status' => 'error',
                'message' => "'La méthode n'est pas supportée. Utilisez du GET"
            ]);
            return false;
        }

        $query = trim($_GET['query'] ?? '');
        if (empty($query))
        {
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => 'Champ de recherche vide.'
            ]);
            return false;
        }

        $spectacleSearch = new SpectacleSearch();
        $results = $spectacleSearch->search($query);

        echo json_encode([
            'status' => 'success',
            'results' => $results,
            'totalResults' => count($results)
        ]);
        return true;
    }

    public function apiListSpectacles()
    {
        header('Content-Type: application/json');

        if (!UserConnectionUtils::isAdminConnected()) 
        {
            // Définir un code HTTP 405 (Unauthorized)
            http_response_code(405);

            // On setup le message d'erreur pour la vue
            echo json_encode(
            [
                'status' => 'error',
                'message' => "Il faut se connecter en tant qu'administrateur pour utiliser ce endpoint"
            ]);

            return false;
        }

        if (RequestUtils::isGetMethod() == false)
        {
            http_response_code(405);
            echo json_encode
            ([
                'status' => 'error',
                'message' => "Seule la méthode GET est supportée",
            ]);
            return false;
        }

        $spectacleList = new SpectacleList();
        $spectacles = $spectacleList->getSpectacleList();

        echo json_encode
        ([
            'status' => 'success',
            'spectacles' => $spectacles,
        ]);

        return true;
    }

    public function apiGetAllSpectacles()
    {
        header('Content-Type: application/json');

        if (!UserConnectionUtils::isAdminConnected()) 
        {
            // Définir un code HTTP 405 (Unauthorized)
            http_response_code(405);

            // On setup le message d'erreur pour la vue
            echo json_encode(
            [
                'status' => 'error',
                'message' => "Il faut se connecter en tant qu'administrateur pour utiliser ce endpoint"
            ]);

            return false;
        }

        if (RequestUtils::isGetMethod() == false)
        {
            http_response_code(405);
            echo json_encode
            ([
                'status' => 'error',
                'message' => "Seule la méthode GET est supportée",
            ]);
            return false;
        }

        $spectacles = SpectacleList::getAllSpectacleNameAndId();
        echo json_encode
        ([
            'status'        => 'success',
            'spectacles'    => $spectacles
        ]);
        return true;
    }

    /**
     * Liste tous les spectacles avec un système de pagination dans l'ordre suivant :
     * 1 - Les spectacles sans séances
     * 2 - Les spectacles en cours (donc avec des séances)
     * 3 - Les spectacles cloturés
     */
    public function listSpectacles()
    {
        if (RequestUtils::isGetMethod() == false)
        {
            http_response_code(405);
            ViewRenderer::error(new MessageErreur("Chargement de la page impossible", "Méthode non supportée"));
            return false;
        }

        header('Content-Type: application/json');

        $page = max(1, (int)($_GET['page'] ?? 1));
        $limit = 20;

        $spectacleList = new SpectacleList($page, $limit);
        $result = $spectacleList->getSpectacleList();

        echo json_encode([
            'status' => 'success',
            'page' => $result->getPage(),
            'total' => $result->getTotal(),
            'spectacles' => $result->getSpectacles()
        ]);
        return true;
    }
}    
