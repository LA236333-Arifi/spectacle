<?php

class SpectacleViewer
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance()->getConnection();
    }

    public function getSpectacleById(int $spectacleId): ?array
    {
        // Récupérer les infos du spectacle
        $querySpectacle = "SELECT s.*, t.nom_type_spectacle, g.nom_groupe, g.date_formation_groupe
                           FROM Spectacle s
                           INNER JOIN Type_Spectacle t ON s.type_spectacle_id = t.type_spectacle_id
                           INNER JOIN Groupe_Spectacle g ON s.groupe_id = g.groupe_id
                           WHERE s.spectacle_id = ?";
        $stmt = $this->pdo->prepare($querySpectacle);
        $stmt->execute([$spectacleId]);
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
        $stmt->execute([$spectacleId]);
        $spectacle['seances'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Récupérer auteur/metteur en scène si type_id = 1, 4 ou 5
        if (in_array((int)$spectacle['type_spectacle_id'], [1, 4, 5]))
        {
            $queryAuteur = "SELECT nom_auteur, nom_metteur_en_scene
                            FROM Auteur_Spectacle
                            WHERE spectacle_id = ?";
            $stmt = $this->pdo->prepare($queryAuteur);
            $stmt->execute([$spectacleId]);
            $auteur = $stmt->fetch(PDO::FETCH_ASSOC);

            $spectacle['nom_auteur'] = $auteur['nom_auteur'] ?? null;
            $spectacle['nom_metteur_en_scene'] = $auteur['nom_metteur_en_scene'] ?? null;
        }

        return $spectacle;
    }
}
