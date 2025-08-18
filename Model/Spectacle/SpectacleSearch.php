<?php

class SpectacleSearch
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance()->getConnection();
    }

    /**
     * Recherche des spectacles par mots-clés (nom ou texte accroche)
     * @param string $keywords
     */
    public function search(string $keywords)
    {
        $query = "
            SELECT s.*,
                g.nom_groupe,
                COUNT(se.seance_id) AS nb_seances
            FROM Spectacle s
            LEFT JOIN Groupe_Spectacle g 
                ON g.groupe_id = s.groupe_id
            LEFT JOIN Seance se 
                ON se.spectacle_id = s.spectacle_id
            WHERE s.nom_spectacle LIKE :kw
            OR s.texte_accroche_spectacle LIKE :kw
            GROUP BY s.spectacle_id, g.nom_groupe
            ORDER BY s.nom_spectacle
        ";

        $like = '%' . $keywords . '%';
        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(':kw', $like, PDO::PARAM_STR);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}