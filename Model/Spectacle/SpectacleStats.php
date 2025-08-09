<?php

class SpectacleStats
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance()->getConnection();
    }

    /**
     * Retourne les stats sur chaque type de spectacle
     */
    public function getStats()
    {
        // Récupérer tous les types de spectacle
        $queryType = "SELECT type_spectacle_id, nom_type_spectacle FROM Type_Spectacle";
        $stmtType = $this->pdo->prepare($queryType);
        $stmtType->execute();
        $types = $stmtType->fetchAll(PDO::FETCH_ASSOC);

        $stats = [];

        // Nombre total de spectacles (pour la répartition)
        $queryTotalSpectaclesAll = "SELECT COUNT(*) FROM Spectacle";
        $stmtTotalSpectaclesAll = $this->pdo->prepare($queryTotalSpectaclesAll);
        $stmtTotalSpectaclesAll->execute();
        $totalSpectaclesAll = (int)$stmtTotalSpectaclesAll->fetchColumn();

        foreach ($types as $type)
        {
            $typeId = $type['type_spectacle_id'];
            $nomType = $type['nom_type_spectacle'];

            // Nombre total de spectacles pour ce type
            $queryTotalSpectacles = "SELECT COUNT(*) FROM Spectacle WHERE type_spectacle_id = ?";
            $stmtTotalSpectacles = $this->pdo->prepare($queryTotalSpectacles);
            $stmtTotalSpectacles->execute([$typeId]);
            $totalSpectacles = (int)$stmtTotalSpectacles->fetchColumn();

            // Nombre total de séances pour ce type
            $queryTotalSeances = "SELECT COUNT(*) 
                FROM Seance s
                INNER JOIN Spectacle sp ON s.spectacle_id = sp.spectacle_id
                WHERE sp.type_spectacle_id = ?";
            $stmtTotalSeances = $this->pdo->prepare($queryTotalSeances);
            $stmtTotalSeances->execute([$typeId]);
            $totalSeances = (int)$stmtTotalSeances->fetchColumn();

            // Nombre de séances en cours (date > NOW() et statut != Annulé)
            $querySeancesEnCours = "SELECT COUNT(*) 
                FROM Seance s
                INNER JOIN Spectacle sp ON s.spectacle_id = sp.spectacle_id
                WHERE sp.type_spectacle_id = ?
                AND s.date_soiree_seance > NOW()
                AND s.statut_seance_id != ?";
            $stmtSeancesEnCours = $this->pdo->prepare($querySeancesEnCours);
            $stmtSeancesEnCours->execute([$typeId, SeanceStatut::Annuler]);
            $seancesEnCours = (int)$stmtSeancesEnCours->fetchColumn();

            // Répartition : pourcentage du nombre de spectacles de ce type par rapport au total
            $repartition = $totalSpectaclesAll > 0 ? round(($totalSpectacles / $totalSpectaclesAll) * 100, 2) : 0;

            $stats[$typeId] = [
                "nom" => $nomType,
                "repartition" => $repartition,
                "seance_en_cours" => $seancesEnCours,
                "seance_total" => $totalSeances
            ];
        }

        return $stats;
    }
}
