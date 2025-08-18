<?php

class SpectacleViewer
{
    private $pdo;
    private $spectacleId;

    public function __construct($spectacleId)
    {
        $this->pdo = Database::getInstance()->getConnection();
        $this->spectacleId = $spectacleId;
    }

    public function getSpectacleData(): ?array
    {
        // Récupérer les infos du spectacle
        $querySpectacle = "SELECT s.*, t.nom_type_spectacle, g.nom_groupe, g.date_formation_groupe
                           FROM Spectacle s
                           INNER JOIN Type_Spectacle t ON s.type_spectacle_id = t.type_spectacle_id
                           INNER JOIN Groupe_Spectacle g ON s.groupe_id = g.groupe_id
                           WHERE s.spectacle_id = ?";
        $stmt = $this->pdo->prepare($querySpectacle);
        $stmt->execute([$this->spectacleId]);
        $spectacle = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$spectacle)
        {
            return null;
        }

        // Récupérer les performeurs du groupe
        $queryPerformeurs = "SELECT p.performeur_id, p.nom_performeur, p.prenom_performeur, r.nom_role_performeur
                             FROM Liaison_Groupe lg
                             INNER JOIN Performeur_Spectacle p ON lg.performeur_id = p.performeur_id
                             INNER JOIN Role_Performeur r ON p.role_performeur_id = r.role_performeur_id
                             WHERE lg.groupe_id = ?";
        $stmt = $this->pdo->prepare($queryPerformeurs);
        $stmt->execute([$spectacle['groupe_id']]);
        $spectacle['performeurs'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Récupérer les séances liées au spectacle
        $querySeances = "SELECT seance_id, date_soiree_seance, statut_seance_id
                         FROM Seance
                         WHERE spectacle_id = ?
                         ORDER BY date_soiree_seance ASC";
        $stmt = $this->pdo->prepare($querySeances);
        $stmt->execute([$this->spectacleId]);
        $spectacle['seances'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Récupérer auteur/metteur en scène si type_id = 1, 4 ou 5
        if (SpectacleType::needsAuteur((int)$spectacle['type_spectacle_id']))
        {
            $queryLiaison = "SELECT auteur_id, metteur_scene_id
                            FROM Auteur_MetteurScene_Spectacle
                            WHERE spectacle_id = ?";
            $stmt = $this->pdo->prepare($queryLiaison);
            $stmt->execute([$this->spectacleId]);
            $liaison = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($liaison) 
            {
                // Auteur
                $queryAuteur = "SELECT nom_auteur, prenom_auteur FROM Auteur_Spectacle WHERE auteur_id = ?";
                $stmtAuteur = $this->pdo->prepare($queryAuteur);
                $stmtAuteur->execute([$liaison['auteur_id']]);
                $auteur = $stmtAuteur->fetch(PDO::FETCH_ASSOC);

                // Metteur en scène
                $queryMetteur = "SELECT nom_metteur_scene, prenom_metteur_scene FROM MetteurScene_Spectacle WHERE metteur_scene_id = ?";
                $stmtMetteur = $this->pdo->prepare($queryMetteur);
                $stmtMetteur->execute([$liaison['metteur_scene_id']]);
                $metteur = $stmtMetteur->fetch(PDO::FETCH_ASSOC);

                $spectacle['nom_auteur'] = isset($auteur) ? $auteur['prenom_auteur'] . ' ' . $auteur['nom_auteur'] : null;
                $spectacle['nom_metteur_en_scene'] = isset($metteur) ? $metteur['prenom_metteur_scene'] . ' ' . $metteur['nom_metteur_scene'] : null;
            } 
            else 
            {
                $spectacle['nom_auteur'] = null;
                $spectacle['nom_metteur_en_scene'] = null;
            }
        }

        return $spectacle;
    }
}
