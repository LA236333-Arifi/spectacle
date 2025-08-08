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

    public function ajouterSeance(SeanceData $data)
    {
        $query = "INSERT INTO Seance (date_soiree_seance, date_ajout_seance, utilisateur_id, statut_seance_id, spectacle_id) VALUES (?, NOW(), ?, ?, ?)";
        $stmt = $this->pdo->prepare($query);
        if ($stmt->execute([
            $data->getDateSoiree(),
            $data->getUtilisateurId(),
            $data->getStatutSeanceId(),
            $data->getSpectacleId()
        ]))
        {
            $this->seanceId = $this->pdo->lastInsertId();
            return $this->seanceId;
        }
        return false;
    }
    
    public function deplacerSeance($nouvelleDate)
    {
        $query = "UPDATE Seance SET date_soiree_seance = ? WHERE seance_id = ?";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([$nouvelleDate, $this->seanceId]);

        // Vérifie si une ligne a été modifiée
        return $stmt->rowCount() > 0;
    }
}
