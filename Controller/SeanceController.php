<?php 

require_once 'autoload.php';

class SeanceController
{
    private $security;

    public function __construct()
    {
        $this->security = new Security(true);
    }

    /**
     * Route: GET /seance
     * Affiche la page pour ajouter/déplacer/annuler une séance
     */
    public function index()
    {
        if (RequestUtils::isGetMethod() == false)
        {
            http_response_code(405);
            ViewRenderer::error(new MessageErreur("Méthode non supportée", "La méthode n'est pas supportée."));
            return false;
        }

        if (UserConnectionUtils::isAdminConnected() == false)
        {
            http_response_code(403);
            ViewRenderer::error(new MessageErreur("Chargement de la page impossible", "Il faut être connecté en tant qu'admin pour visualiser cette page."));
            return false;
        }

        $viewData = 
        [
            'token_csrf' => $this->security->genererCSRFToken(),
            'statuts' => SeanceStatut::getSeanceStatutToString()
        ];

        $viewRenderer = new ViewRenderer("View/Gerant/GestionSeance.php", $viewData);
        $viewRenderer->render();
    }

    /**
     * Route: POST /seance/add
     * Ajoute une séance
     */
    public function addSeance()
    {
        header('Content-Type: application/json');

        if (RequestUtils::isPostMethod() == false)
        {
            http_response_code(405);
            echo json_encode
            ([
                'status' => 'error',
                'message' => "Utilisez POST pour ajouter une séance.",
            ]);
            return false;
        }

        if (UserConnectionUtils::isAdminConnected() == false)
        {
            http_response_code(403);
            echo json_encode
            ([
                'status' => 'error',
                'message' => "Il faut être connecté en tant qu'admin pour accéder à cet endpoint",
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

        $dateSoiree = $_POST['date_soiree_seance'] ?? null;
        $spectacleId = $_POST['spectacle_id'] ?? null;
        $utilisateurId = UserConnectionUtils::getConnectedUserId();

        if (empty($dateSoiree) || empty($utilisateurId) || empty($spectacleId))
        {
            echo json_encode(['status' => 'error', 'message' => "Paramètres manquants pour la création de la séance."]);
            return false;
        }

        $seanceData = new SeanceData($dateSoiree, $utilisateurId, $spectacleId);
        $seanceDataValidator = new SeanceDataValidator($seanceData);

        if ($seanceDataValidator->isValid() == false)
        {
            echo json_encode(['status' => 'error', 'message' => "Paramètres manquants pour la création de la séance."]);
            return false;
        }

        $seance = new Seance();
        $resultatSeance = $seance->ajouterSeance($seanceData);

        if ($resultatSeance == SeanceActionResult::Valid)
        {
            http_response_code(200);
            echo json_encode
            ([
                'status' => 'success',
                'message' => "Séance ajoutée avec succès",
            ]);
            return true;
        }
        else if ($resultatSeance == SeanceActionResult::DateDejaPrise)
        {
            echo json_encode
            ([
                'status' => 'error',
                'message' => "La date est déjà occupée par une autre séance",
            ]);
            return false;
        }
        else if ($resultatSeance == SeanceActionResult::DateMauvaisFormat)
        {
            echo json_encode
            ([
                'status' => 'error',
                'message' => "Le format de la date est invalide. Il faut avoir soit DD-MM-YYYY ou YYYY-MM-DD",
            ]);
            return false;
        }

        // Cas final, l'opération a échoué mais on ne sait pas exactement pourquoi
        echo json_encode
        ([
            'status' => 'error',
            'message' => "Impossible d'ajouter la séance. Vérifiez la validité de l'ID",
        ]);
        return false;
    }

    /**
     * Route: POST /seance/move
     * Déplace une séance (change la date)
     */
    public function moveSeance()
    {
        header('Content-Type: application/json');
        if (RequestUtils::isPostMethod() == false)
        {
            http_response_code(405);
            echo json_encode
            ([
                'status' => 'error',
                'message' => "Utilisez POST pour déplacer une séance.",
            ]);
            return false;
        }

        if (UserConnectionUtils::isAdminConnected() == false)
        {
            http_response_code(403);
            echo json_encode
            ([
                'status' => 'error',
                'message' => "Il faut être connecté en tant qu'admin pour accéder à cet endpoint.",
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
        
        $seanceId = $_POST['seance_id'] ?? null;
        $nouvelleDate = $_POST['nouvelle_date_soiree'] ?? null;

        if (empty($seanceId) || empty($nouvelleDate))
        {
            echo json_encode(['status' => 'error', 'message' => "Paramètres manquants pour déplacer la séance."]);
            return false;
        }

        $seanceId = filter_var($seanceId, FILTER_VALIDATE_INT);
        if ($seanceId === false)
        {
            echo json_encode(['status' => 'error', 'message' => "Identifiant de la séance invalide."]);
            return false;
        }

        $seance = new Seance($seanceId);
        $resultatSeance = $seance->deplacerSeance($nouvelleDate);

        if ($resultatSeance == SeanceActionResult::Valid)
        {
            http_response_code(200);
            echo json_encode
            ([
                'status' => 'success',
                'message' => "Séance déplacée avec succès",
            ]);
            return true;
        }
        else if ($resultatSeance == SeanceActionResult::DateDejaPrise)
        {
            echo json_encode
            ([
                'status' => 'error',
                'message' => "La date est déjà occupée par une autre séance",
            ]);
            return false;
        }
        else if ($resultatSeance == SeanceActionResult::DateMauvaisFormat)
        {
            echo json_encode
            ([
                'status' => 'error',
                'message' => "Le format de la date est invalide. Il faut avoir soit DD-MM-YYYY ou YYYY-MM-DD",
            ]);
            return false;
        }

        // Cas final, l'opération a échoué mais on ne sait pas exactement pourquoi
        echo json_encode
        ([
            'status' => 'error',
            'message' => "Impossible de déplacer la séance. Vérifiez la validité de l'ID",
        ]);
        return false;
    }

    /**
     * Route: POST /seance/move
     * Annule une séance 
     */
    public function annulerSeance()
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

        $seanceId = $_POST['seance_id'] ?? null;

        if (empty($seanceId))
        {
            echo json_encode(['status' => 'error', 'message' => "Identifiant de la séance manquant."]);
            return false;
        }

        $seanceId = filter_var($seanceId, FILTER_VALIDATE_INT);
        if ($seanceId === false)
        {
            echo json_encode(['status' => 'error', 'message' => "Identifiant de la séance invalide."]);
            return false;
        }

        $seance = new Seance($seanceId);
        $result = $seance->annulerSeance();
        if ($result == SeanceActionResult::Invalid)
        {
            echo json_encode([
                    'status' => 'error',
                    'message' => "La séance n'a pas pu être annulé"
                ]);
            return false;
        }

        http_response_code(200);
        echo json_encode([
                'status' => 'success',
                'message' => "La séance a été annulée avec succès."
            ]);
        return true;
    }

    public function getSeancesDates()
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

        $spectacleId = $_GET['spectacle_id'] ?? null;
        if (empty($spectacleId))
        {
            echo json_encode(
            [
                'status' => 'error',
                'message' => "Le spectacleId est absent"
            ]);
            return false;
        }

        $spectacleId = filter_var($spectacleId, FILTER_VALIDATE_INT);
        if ($spectacleId === false)
        {
            echo json_encode(
            [
                'status' => 'error',
                'message' => "Le format du spectacleId doit être numérique"
            ]);
            return false;
        }

        $seances = Seance::getAllDatesWithSpectacleId($spectacleId);

        http_response_code(200);
        echo json_encode(
        [
            'status' => 'success',
            'seances' => $seances
        ]);

        return true;
    }

    public function changeSeanceStatut()
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

        $seanceId = $_POST['seance_id'] ?? null;
        $newStatutId = $_POST['seance_statut_id'] ?? null;

        if (empty($seanceId) || empty($newStatutId))
        {
            echo json_encode(['status' => 'error', 'message' => "Identifiant de la séance ou du nouveau statut manquant."]);
            return false;
        }

        $seanceId = filter_var($seanceId, FILTER_VALIDATE_INT);
        $newStatutId = filter_var($newStatutId, FILTER_VALIDATE_INT);
        if ($seanceId === false || $newStatutId === false)
        {
            echo json_encode(['status' => 'error', 'message' => "Identifiant de la séance ou du nouveau statut invalide."]);
            return false;
        }

        if (SeanceStatut::isStatutValid($newStatutId) == false)
        {
            echo json_encode(['status' => 'error', 'message' => "Le nouveau statut de la séance est invalide."]);
            return false;
        }

        $seance = new Seance($seanceId);
        $result = $seance->changerStatutSeance($newStatutId);

        if ($result == SeanceActionResult::Valid)
        {
            http_response_code(200);

            echo json_encode(['status' => 'success', 'message' => "Le statut de la séance a été modifié avec succès"]);
            return true;
        }
        else
        {
            echo json_encode(['status' => 'error', 'message' => "Impossible de changer le statut de la séance. Vérifiez que la date n'est pas déjà passée ou que l'id est correct"]);
            return false;
        }
    }

    public function apiListSeances()
    {
        // On normalise les filtres avec du camel_case pour le validateur et le modèle
        $filters = 
        [
            'date_min'          => $_GET['dateMin']       ?? null,
            'date_max'          => $_GET['dateMax']       ?? null,
            'prix_min'          => $_GET['prixMin']       ?? null,
            'prix_max'          => $_GET['prixMax']       ?? null,
            'duree_min'         => $_GET['dureeMin']      ?? null,
            'duree_max'         => $_GET['dureeMax']      ?? null,
            'types_spectacle'   => !empty($_GET['typeSpectacle']) // On veut un tableau ici
                                ? [$_GET['typeSpectacle']]
                                : [],
            'statut_seance'     => $_GET['statutSeance']  ?? null,
            'page'              => $_GET['page']          ?? null,
            'limit'             => $_GET['limit']         ?? null
        ];

        $validator = new SeanceListValidator($filters);
        if (!$validator->validate()) 
        {
            http_response_code(400);
            echo json_encode([
                'status'  => 'error',
                'message' => 'Filtres invalides',
                'errors'  => $validator->getErrors(),
            ]);

            return false;
        }

        $list = new SeanceList($validator->getPage(), $validator->getLimit());
        $seances = $list->fetch($filters);

        echo json_encode
        ([
            'status'    => 'success',
            'seances'   => $seances
        ]);

        return true;
    }
}
