<?php 

class SeanceController
{
    /**
     * Route: POST /seance/add
     * Ajoute une séance
     */
    public function addSeance()
    {
        if (RequestUtils::isPostMethod() == false)
        {
            http_response_code(405);
            ViewRenderer::error(new MessageErreur("Méthode non supportée", "Utilisez POST pour ajouter une séance."));
            return false;
        }

        http_response_code(400);
        header('Content-Type: application/json');

        $dateSoiree = $_POST['date_soiree_seance'] ?? null;
        $utilisateurId = $_POST['utilisateur_id'] ?? null;
        $statutSeanceId = $_POST['statut_seance_id'] ?? null;
        $spectacleId = $_POST['spectacle_id'] ?? null;

        if (empty($dateSoiree) || empty($utilisateurId) || empty($statutSeanceId) || empty($spectacleId))
        {
            echo json_encode(['status' => 'error', 'message' => "Paramètres manquants pour la création de la séance."]);
            return false;
        }

        $seanceData = new SeanceData($dateSoiree, $statutSeanceId, $utilisateurId, $spectacleId);
        $seanceDataValidator = new SeanceDataValidator($seanceData);

        if ($seanceDataValidator->isValid() == false)
        {
            echo json_encode(['status' => 'error', 'message' => "Paramètres manquants pour la création de la séance."]);
            return false;
        }

        $seance = new Seance();
        $seanceId = $seance->ajouterSeance($seanceData);

        if ($seanceId)
        {
            http_response_code(200);
            echo json_encode(['status' => 'success', 'message' => "Séance ajoutée avec succès", 'seance_id' => $seanceId]);
            return true;
        }
        else
        {
            echo json_encode(['status' => 'error', 'message' => "Impossible d'ajouter la séance."]);
            return false;
        }
    }

    /**
     * Route: POST /seance/move
     * Déplace une séance (change la date)
     */
    public function moveSeance()
    {
        if (RequestUtils::isPostMethod() == false)
        {
            http_response_code(405);
            ViewRenderer::error(new MessageErreur("Méthode non supportée", "Utilisez POST pour déplacer une séance."));
            return false;
        }

        http_response_code(400);
        header('Content-Type: application/json');

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
        $success = $seance->deplacerSeance($nouvelleDate);

        if ($success)
        {
            echo json_encode(['status' => 'success', 'message' => "Séance déplacée avec succès"]);
            return true;
        }
        else
        {
            echo json_encode(['status' => 'error', 'message' => "Impossible de déplacer la séance."]);
            return false;
        }
    }
}
