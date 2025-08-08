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

    public function isDateSeancePlanifiee($date)
    {
        $query = "SELECT COUNT(*) FROM Seance WHERE date_soiree_seance = ? AND statut_seance_id = 1";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([$date]);
        return $stmt->fetchColumn() > 0;
    }

    public function ajouterSeance(SeanceData $data)
    {
        if ($this->isDateSeancePlanifiee($data->getDateSoiree()))
        {
            return false;
        }

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
        if ($this->isDateSeancePlanifiee($nouvelleDate))
        {
            return false;
        }

        $query = "UPDATE Seance SET date_soiree_seance = ? WHERE seance_id = ?";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([$nouvelleDate, $this->seanceId]);
        return $stmt->rowCount() > 0;
    }

    public function annulerSeance()
    {
        $query = "UPDATE Seance SET statut_seance_id = 2 WHERE seance_id = ?";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([$this->seanceId]);

        // Vérifie si une ligne a été modifiée
        return $stmt->rowCount() > 0;
    }

    public function supprimerSeance()
    {
        $query = "DELETE FROM Seance WHERE seance_id = ?";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([$this->seanceId]);

        // Vérifie si une ligne a été modifiée
        return $stmt->rowCount() > 0;
    }
}
