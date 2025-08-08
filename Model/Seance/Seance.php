<?php

class Seance
{
    private $seanceId;
    private $pdo;

    public function __construct($seanceId = null)
    {
        $this->seanceId = $seanceId;
        $this->pdo = Database::getInstance()->getConnection();
    }

    public function getSeanceData()
    {
        $query = "SELECT date_soiree_seance, date_ajout_seance, utilisateur_id, statut_seance_id, spectacle_id FROM Seance WHERE seance_id = ?";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([$this->seanceId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row)
        {
            return new SeanceData(
                $row['date_soiree_seance'],
                (int)$row['statut_seance_id'],
                (int)$row['utilisateur_id'],
                (int)$row['spectacle_id']
            );
        }
        return null;
    }
}
