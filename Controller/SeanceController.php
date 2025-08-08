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

}
