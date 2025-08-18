<?php

enum SeanceActionResult
{
    case Valid; // Tout est bon, l'action est un succès
    case DateDejaPrise; // La date est déjà prise par une autre séance
    case DateMauvaisFormat; // Mauvais format de date
    case Invalid; // On se sait pas exactement ce qui a causé le problème
}

class Seance
{
    private $seanceId;
    private $pdo;

    public function __construct($seanceId = null)
    {
        $this->seanceId = $seanceId;
        $this->pdo = Database::getInstance()->getConnection();
    }

    public function getSeanceId()
    {
        return $this->seanceId;
    }

    public static function getAllDatesWithSpectacleId($spectacleId)
    {
        // Connexion à la base de données
        $db = Database::getInstance()->getConnection();

        // Requête SQL pour récupérer les dates et les IDs des séances du spectacle spécifié
        $sql = "
            SELECT se.seance_id, se.date_soiree_seance
            FROM Seance se
            JOIN Spectacle sp ON se.spectacle_id = sp.spectacle_id
            WHERE sp.spectacle_id = ? 
            AND sp.statut_spectacle_id IN (1, 2) 
            AND se.statut_seance_id = 1 
            ORDER BY se.date_soiree_seance ASC
        ";

        // Préparer et exécuter la requête avec le paramètre $spectacleId
        $stmt = $db->prepare($sql);
        $stmt->execute([$spectacleId]);

        // Récupérer tous les résultats
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function isDateSeancePlanifiee($date)
    {
        $query = "SELECT COUNT(*) FROM Seance WHERE date_soiree_seance = ? AND statut_seance_id = 1";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([DateUtils::normalizeDate($date)]);
        return $stmt->fetchColumn() > 0;
    }

    public function ajouterSeance(SeanceData $data)
    {
        $nouvelleDate = DateUtils::normalizeDate($data->getDateSoiree());
        if ($nouvelleDate == null)
        {
            return SeanceActionResult::DateMauvaisFormat;
        }
        if ($this->isDateSeancePlanifiee($nouvelleDate))
        {
            return SeanceActionResult::DateDejaPrise;
        }

        $query = "INSERT INTO Seance (date_soiree_seance, date_ajout_seance, utilisateur_id, statut_seance_id, spectacle_id) VALUES (?, NOW(), ?, ?, ?)";
        $stmt = $this->pdo->prepare($query);
        $result = $stmt->execute
        ([
            $nouvelleDate,
            $data->getUtilisateurId(),
            SeanceStatut::Planifier,
            $data->getSpectacleId()
        ]);

        if ($result)
        {
            $this->seanceId = $this->pdo->lastInsertId();

            // Mettre à jour le statut du spectacle à "En cours" (2)
            $queryUpdateSpectacle = "UPDATE Spectacle SET statut_spectacle_id = 2 WHERE spectacle_id = ? AND statut_spectacle_id != 2";
            $stmtUpdate = $this->pdo->prepare($queryUpdateSpectacle);
            $stmtUpdate->execute([$data->getSpectacleId()]);

            return SeanceActionResult::Valid;
        }

        return SeanceActionResult::Invalid;
    }

    public function deplacerSeance($nouvelleDate)
    {
        $nouvelleDate = DateUtils::normalizeDate($nouvelleDate);
        if ($nouvelleDate == null)
        {
            return SeanceActionResult::DateMauvaisFormat;
        }

        if ($this->isDateSeancePlanifiee($nouvelleDate))
        {
            return SeanceActionResult::DateDejaPrise;
        }

        $query = "UPDATE Seance SET date_soiree_seance = ? WHERE seance_id = ?";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([$nouvelleDate, $this->seanceId]);
        return ($stmt->rowCount() > 0) ? SeanceActionResult::Valid : SeanceActionResult::Invalid; 
    }

    public function annulerSeance()
    {
        $query = "UPDATE Seance 
          SET statut_seance_id = 2 
          WHERE seance_id = ? 
          AND date_soiree_seance >= CURDATE()";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([$this->seanceId]);

        // Vérifie si une ligne a été modifiée
        return ($stmt->rowCount() > 0) ? SeanceActionResult::Valid : SeanceActionResult::Invalid; 
    }

    public function changerStatutSeance($newStatut)
    {
        $query = "UPDATE Seance 
          SET statut_seance_id = ? 
          WHERE seance_id = ? 
          AND date_soiree_seance >= CURDATE()";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([$newStatut, $this->seanceId]);

        // Vérifie si une ligne a été modifiée
        return ($stmt->rowCount() > 0) ? SeanceActionResult::Valid : SeanceActionResult::Invalid; 
    }

    public function supprimerSeance()
    {
        $query = "DELETE FROM Seance WHERE seance_id = ?";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([$this->seanceId]);

        // Vérifie si une ligne a été modifiée
        return ($stmt->rowCount() > 0) ? SeanceActionResult::Valid : SeanceActionResult::Invalid; 
    }
}
