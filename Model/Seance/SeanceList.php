<?php

class SeanceList
{
    private $pdo;
    private $page;
    private $limit;

    public function __construct($page = 1, $limit = 20)
    {
        $this->pdo = Database::getInstance()->getConnection();
        $this->page = $page;
        $this->limit = $limit;
    }

    /**
     * Retourne la liste paginée des séances
     * @param int $page
     * @param int $limit
     */
    public function getListSeances(): SeanceListResult
    {
        $offset = ($this->page - 1) * $this->limit;

        // Récupérer le total
        $queryCount = "SELECT COUNT(*) FROM Seance";
        $stmtCount = $this->pdo->prepare($queryCount);
        $stmtCount->execute();
        $total = (int)$stmtCount->fetchColumn();

        // Récupérer les séances paginées
        $query = "SELECT seance_id, date_soiree_seance, date_ajout_seance, utilisateur_id, statut_seance_id, spectacle_id
                FROM Seance
                WHERE date_soiree_seance >= CURDATE() 
                ORDER BY date_soiree_seance DESC
                LIMIT ? OFFSET ?";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(1, $this->limit, PDO::PARAM_INT);
        $stmt->bindValue(2, $offset, PDO::PARAM_INT);

        $stmt->execute();
        $seances = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return new SeanceListResult($this->page, $total, $seances);
    }
}
