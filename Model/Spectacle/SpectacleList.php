<?php

class SpectacleList
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance()->getConnection();
    }

    public static function getAllSpectacleNameAndId()
    {
        $db = Database::getInstance()->getConnection();
        $sql = "
            SELECT spectacle_id, nom_spectacle
            FROM Spectacle
            WHERE statut_spectacle_id != 3
            ORDER BY nom_spectacle ASC
        ";
        
        $stmt = $db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getSpectacleList()
    {
        // On récupère les infos de base du spectacle + nom du groupe + nb séances
        $query = "
            SELECT 
                s.spectacle_id,
                s.nom_spectacle,
                s.texte_accroche_spectacle,
                s.prix_spectacle,
                s.duree_minutes_spectacle,
                s.type_spectacle_id,
                g.nom_groupe,
                (
                    SELECT COUNT(*) 
                    FROM Seance se 
                    WHERE se.spectacle_id = s.spectacle_id
                ) AS nb_seances
            FROM Spectacle s
            INNER JOIN Groupe_Spectacle g ON g.groupe_id = s.groupe_id
            WHERE s.statut_spectacle_id = 2
            ORDER BY s.spectacle_id ASC
        ";

        $stmt = $this->pdo->prepare($query);
        $stmt->execute();
        $spectacles = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($spectacles as &$spectacle) {
            $spectacleId = (int)$spectacle['spectacle_id'];

            // Récupérer la liste des performeurs + rôle
            $qPerf = "
                SELECT 
                    p.nom_performeur,
                    p.prenom_performeur,
                    r.nom_role_performeur
                FROM Liaison_Groupe lg
                INNER JOIN Performeur_Spectacle p ON p.performeur_id = lg.performeur_id
                INNER JOIN Role_Performeur r ON r.role_performeur_id = p.role_performeur_id
                WHERE lg.groupe_id = (
                    SELECT groupe_id FROM Spectacle WHERE spectacle_id = :spectacle_id
                )
            ";
            $stmtPerf = $this->pdo->prepare($qPerf);
            $stmtPerf->execute([':spectacle_id' => $spectacleId]);
            $spectacle['performeurs'] = $stmtPerf->fetchAll(PDO::FETCH_ASSOC);

            // Si le type de spectacle requiert un auteur/metteur en scène
            if (SpectacleType::needsAuteur((int)$spectacle['type_spectacle_id'])) {
                $qAuteur = "
                    SELECT 
                        a.nom_auteur,
                        a.prenom_auteur,
                        m.nom_metteur_scene,
                        m.prenom_metteur_scene
                    FROM Auteur_MetteurScene_Spectacle am
                    INNER JOIN Auteur_Spectacle a ON a.auteur_id = am.auteur_id
                    INNER JOIN MetteurScene_Spectacle m ON m.metteur_scene_id = am.metteur_scene_id
                    WHERE am.spectacle_id = :spectacle_id
                ";
                $stmtAuteur = $this->pdo->prepare($qAuteur);
                $stmtAuteur->execute([':spectacle_id' => $spectacleId]);
                $spectacle['auteur_metteur'] = $stmtAuteur->fetch(PDO::FETCH_ASSOC) ?: null;
            } else {
                $spectacle['auteur_metteur'] = null;
            }
        }

        return $spectacles;
    }
}
