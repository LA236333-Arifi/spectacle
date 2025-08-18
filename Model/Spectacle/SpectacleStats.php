<?php

class SpectacleStats
{
    private PDO $pdo;
    private int $year;

    public function __construct(int $year)
    {
        $this->pdo = Database::getInstance()->getConnection();
        $this->year = $year;
    }

    public function getStatsParType(): array
    {
        // Récupérer tous les types de spectacle
        $queryType = "SELECT type_spectacle_id, nom_type_spectacle 
                      FROM Type_Spectacle";
        $stmtType = $this->pdo->query($queryType);
        $types = $stmtType->fetchAll(PDO::FETCH_ASSOC);

        $stats = [];

        // Total des séances planifiées pour l'année (pour le %)
        $sqlTotalPlannedYear = "
            SELECT COUNT(*) 
            FROM Seance s
            INNER JOIN Spectacle sp ON s.spectacle_id = sp.spectacle_id
            WHERE YEAR(s.date_soiree_seance) = :year
              AND s.statut_seance_id != :annule
        ";
        $stmtTotal = $this->pdo->prepare($sqlTotalPlannedYear);
        $stmtTotal->execute([
            ':year'   => $this->year,
            ':annule' => SeanceStatut::Annuler
        ]);
        $totalPlannedYear = (int)$stmtTotal->fetchColumn();
        $stats['totalPlannedYear'] = $totalPlannedYear;

        foreach ($types as $type)
        {
            $typeId = $type['type_spectacle_id'];
            $nomType = $type['nom_type_spectacle'];

            // Séances planifiées pour ce type et cette année
            $sqlPlanned = "
                SELECT COUNT(*)
                FROM Seance s
                INNER JOIN Spectacle sp ON s.spectacle_id = sp.spectacle_id
                WHERE sp.type_spectacle_id = :typeId
                  AND YEAR(s.date_soiree_seance) = :year
                  AND s.date_soiree_seance >= CURDATE()
                  AND s.statut_seance_id != :annule
            ";
            $stmtPlanned = $this->pdo->prepare($sqlPlanned);
            $stmtPlanned->execute([
                ':typeId' => $typeId,
                ':year'   => $this->year,
                ':annule' => SeanceStatut::Annuler
            ]);
            $planned = (int)$stmtPlanned->fetchColumn();

            // Total par type (sans annulées)
            $sqlTotalType = "
                SELECT COUNT(*)
                FROM Seance s
                INNER JOIN Spectacle sp ON s.spectacle_id = sp.spectacle_id
                WHERE sp.type_spectacle_id = :typeId
                AND YEAR(s.date_soiree_seance) = :year
                AND s.statut_seance_id != :annule";
            $stmtTotalType = $this->pdo->prepare($sqlTotalType);
            $stmtTotalType->execute([
                ':typeId' => $typeId,
                ':year'   => $this->year,
                ':annule' => SeanceStatut::Annuler
            ]);
            $totalSinceStart = (int)$stmtTotalType->fetchColumn();

            // Pourcentage
            $percent = $totalPlannedYear > 0 ? round(($totalSinceStart / $totalPlannedYear) * 100, 2) : 0;
            $seancesDejaFinies = $totalSinceStart - $planned;

            $stats['seances'][] = 
            [
                'type'              => $nomType,
                'percent'           => $percent,
                'planned'           => $planned,
                'ended'             => $seancesDejaFinies,
                'plannedSinceStart' => $totalSinceStart
            ];
        }

        return $stats;
    }

    public function getStatsParMois(): array
    {
        // Total annuel planifié (hors annulées)
        $sqlTotal = "
            SELECT COUNT(*) 
            FROM Seance s
            INNER JOIN Spectacle sp ON s.spectacle_id = sp.spectacle_id
            WHERE YEAR(s.date_soiree_seance) = :year
            AND s.statut_seance_id != :annule
        ";
        $stmtTotal = $this->pdo->prepare($sqlTotal);
        $stmtTotal->execute([
            ':year'   => $this->year,
            ':annule' => SeanceStatut::Annuler
        ]);
        $totalYearPlanned = (int)$stmtTotal->fetchColumn();

        // Initialiser la structure pour 12 mois
        $months = [];
        for ($mois = 1; $mois <= 12; $mois++) 
        {
            $months[$mois] = 
            [
                'planned'       => 0,
                'cancelled'     => 0,
                'byType'        => [],
                'percentOfYear' => 0
            ];
        }

        // Planifié + Annulé par mois
        $sqlMonthTotals = "
            SELECT MONTH(s.date_soiree_seance) AS m,
                SUM(CASE WHEN s.statut_seance_id != :annule THEN 1 ELSE 0 END) AS planned,
                SUM(CASE WHEN s.statut_seance_id = :annule THEN 1 ELSE 0 END) AS cancelled
            FROM Seance s
            INNER JOIN Spectacle sp ON s.spectacle_id = sp.spectacle_id
            WHERE YEAR(s.date_soiree_seance) = :year
            GROUP BY MONTH(s.date_soiree_seance)
        ";
        $stmtMonthTotals = $this->pdo->prepare($sqlMonthTotals);
        $stmtMonthTotals->execute([
            ':year'   => $this->year,
            ':annule' => SeanceStatut::Annuler
        ]);
        
        while ($row = $stmtMonthTotals->fetch(PDO::FETCH_ASSOC)) 
        {
            $mois = (int)$row['m'];
            $months[$mois]['planned']   = (int)$row['planned'];
            $months[$mois]['cancelled'] = (int)$row['cancelled'];
        }

        // Détail par type et par mois (seulement planifiées)
        $sqlByType = "
            SELECT MONTH(s.date_soiree_seance) AS m,
                ts.nom_type_spectacle AS type,
                COUNT(*) AS nb
            FROM Seance s
            INNER JOIN Spectacle sp ON s.spectacle_id = sp.spectacle_id
            INNER JOIN Type_Spectacle ts ON sp.type_spectacle_id = ts.type_spectacle_id
            WHERE YEAR(s.date_soiree_seance) = :year
            AND s.statut_seance_id != :annule
            GROUP BY MONTH(s.date_soiree_seance), ts.nom_type_spectacle
        ";
        $stmtByType = $this->pdo->prepare($sqlByType);
        $stmtByType->execute([
            ':year'   => $this->year,
            ':annule' => SeanceStatut::Annuler
        ]);

        while ($row = $stmtByType->fetch(PDO::FETCH_ASSOC)) 
        {
            $mois = (int)$row['m'];
            $months[$mois]['byType'][$row['type']] = (int)$row['nb'];
        }

        // Calcul du pourcentage
        if ($totalYearPlanned > 0) 
        {
            foreach ($months as $mois => &$data) 
            {
                $data['percentOfYear'] = round(($data['planned'] / $totalYearPlanned) * 100, 1);
            }
        }

        return 
        [
            'year'             => $this->year,
            'totalYearPlanned' => $totalYearPlanned,
            'months'           => $months
        ];
    }

}
