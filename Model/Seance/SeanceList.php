<?php

class SeanceList
{
    private $pdo;
    private $page;
    private $limit;

    public function __construct($page = 1, $limit = 20)
    {
        $this->pdo   = Database::getInstance()->getConnection();
        $this->page  = max(1, $page);
        $this->limit = max(1, $limit);
    }

    public function setPage($page)
    {
        $this->page = max(1, $page);
    }

    public function setLimit($limit)
    {
        $this->limit = max(1, $limit);
    }

    public function fetch($filters)
    {
        $offset = ($this->page - 1) * $this->limit;
        $where = [];
        $params = [];

        if (empty($filters['date_min'])) {
            $filters['date_min'] = date('Y-m-d');
        }
        $where[] = "s.date_soiree_seance >= :date_min";
        $params[':date_min'] = $filters['date_min'];

        if (!empty($filters['date_max'])) {
            $where[] = "s.date_soiree_seance <= :date_max";
            $params[':date_max'] = $filters['date_max'];
        }
        if (!empty($filters['prix_min'])) {
            $where[] = "sp.prix_spectacle >= :prix_min";
            $params[':prix_min'] = $filters['prix_min'];
        }
        if (!empty($filters['prix_max'])) {
            $where[] = "sp.prix_spectacle <= :prix_max";
            $params[':prix_max'] = $filters['prix_max'];
        }
        if (!empty($filters['duree_min'])) {
            $where[] = "sp.prix_spectacle >= :duree_min";
            $params[':duree_min'] = $filters['duree_min'];
        }
        if (!empty($filters['duree_max'])) {
            $where[] = "sp.prix_spectacle <= :duree_max";
            $params[':duree_max'] = $filters['duree_max'];
        }
        if (!empty($filters['types_spectacle'])) {
            $filters['types_spectacle'] = array_unique($filters['types_spectacle']);
            $placeholders = [];
            foreach ($filters['types_spectacle'] as $i => $val) {
                $ph = ":type_$i";
                $placeholders[] = $ph;
                $params[$ph] = $val;
            }
            $where[] = "ts.type_spectacle_id IN (".implode(',', $placeholders).")";
        }

        if (!empty($filters['statut_seance'])) 
        {
            $where[] = "ss.statut_seance_id = :statut";
            $params[':statut'] = $filters['statut_seance'];
        } 
        else 
        {
            // Valeur par défaut au statut "Planifier" si absent
            $where[] = "ss.statut_seance_id = :statut";
            $params[':statut'] = SeanceStatut::Planifier; 
        }

        $whereSql = $where ? 'WHERE '.implode(' AND ', $where) : '';

        // 1. Requête COUNT pour pagination
        $countSql = "SELECT COUNT(*) as total
                    FROM Seance s
                    JOIN Spectacle sp ON sp.spectacle_id = s.spectacle_id
                    JOIN Statut_Seance ss ON ss.statut_seance_id = s.statut_seance_id
                    JOIN Type_Spectacle ts ON ts.type_spectacle_id = sp.type_spectacle_id
                    JOIN Groupe_Spectacle g ON g.groupe_id = sp.groupe_id
                    $whereSql";

        $countStmt = $this->pdo->prepare($countSql);
        foreach ($params as $k => $v) 
        {
            $countStmt->bindValue($k, $v);
        }
        $countStmt->execute();

        $totalItems = (int)$countStmt->fetchColumn();
        $totalPages = (int)ceil($totalItems / $this->limit);

        $sql = "SELECT s.seance_id, s.date_soiree_seance, ss.nom_statut_seance,
                       sp.spectacle_id, sp.nom_spectacle, sp.texte_accroche_spectacle, sp.prix_spectacle, sp.duree_minutes_spectacle,
                       ts.type_spectacle_id, ts.nom_type_spectacle,
                       g.groupe_id, g.nom_groupe
                FROM Seance s
                JOIN Spectacle sp ON sp.spectacle_id = s.spectacle_id
                JOIN Statut_Seance ss ON ss.statut_seance_id = s.statut_seance_id
                JOIN Type_Spectacle ts ON ts.type_spectacle_id = sp.type_spectacle_id
                JOIN Groupe_Spectacle g ON g.groupe_id = sp.groupe_id
                $whereSql
                ORDER BY s.date_soiree_seance ASC
                LIMIT :limit OFFSET :offset";

        $stmt = $this->pdo->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->bindValue(':limit', (int)$this->limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($rows as &$row) {
            $row['membres'] = $this->fetchMembres($row['groupe_id']);
            if (SpectacleType::needsAuteur($row['type_spectacle_id'])) {
                $row['auteur_metteur_scene'] = $this->fetchAuteurMetteurScene($row['spectacle_id']);
            }
        }

        return 
        [
            'totalItems' => $totalItems,
            'totalPages' => $totalPages,
            'items' => $rows
        ];
    }

    private function fetchMembres($groupeId)
    {
        $sql = "SELECT p.nom_performeur, p.prenom_performeur, r.nom_role_performeur
                FROM Liaison_Groupe lg
                JOIN Performeur_Spectacle p ON p.performeur_id = lg.performeur_id
                JOIN Role_Performeur r ON r.role_performeur_id = p.role_performeur_id
                WHERE lg.groupe_id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$groupeId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function fetchAuteurMetteurScene($spectacleId)
    {
        $sql = "SELECT a.nom_auteur, a.prenom_auteur, m.nom_metteur_scene, m.prenom_metteur_scene
                FROM Auteur_MetteurScene_Spectacle ams
                JOIN Auteur_Spectacle a ON a.auteur_id = ams.auteur_id
                JOIN MetteurScene_Spectacle m ON m.metteur_scene_id = ams.metteur_scene_id
                WHERE ams.spectacle_id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$spectacleId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
