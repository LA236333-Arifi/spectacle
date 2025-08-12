<?php

class SeanceList
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance()->getConnection();
    }

    /**
     * Retourne la liste paginée des séances
     * @param int $page
     * @param int $limit
     */
    public function getListSeances(int $page = 1, int $limit = 20): SeanceListResult
    {
        $offset = ($page - 1) * $limit;

        // Récupérer le total
        $queryCount = "SELECT COUNT(*) FROM Seance";
        $stmtCount = $this->pdo->prepare($queryCount);
        $stmtCount->execute();
        $total = (int)$stmtCount->fetchColumn();

        // Récupérer les séances paginées
        $query = "SELECT seance_id, date_soiree_seance, date_ajout_seance, utilisateur_id, statut_seance_id, spectacle_id
                FROM Seance
                ORDER BY date_soiree_seance DESC
                LIMIT ? OFFSET ?";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue($limit, PDO::PARAM_INT);
        $stmt->bindValue($offset, PDO::PARAM_INT);

        $stmt->execute();
        $seances = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return new SeanceListResult($page, $total, $seances);
    }
}
